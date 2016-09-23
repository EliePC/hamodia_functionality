<?php
/*
1. Get weather info
2. Get currency info
3. Get stocks info
4. Get hebrew date
*/

class Hamodia_APIs
{
    protected static $currency_exchange_app_id = '20a82832ae3743c78fc5ec2914e8732b';

    protected static $hebrew_dates_cache = [];

    // 1. Get weather info
    public static function get_weather_info($search = '11219')
    {
        $search = urlencode(strtolower($search));

        $trans_key = 'weather_' . $search;

        // Try the cache first
        if ($data = get_transient($trans_key)) {
            return $data;
        }

        // There's no cache, so let's query worldweatheronline
        $get_params = http_build_query([
            'key'         => '2uc25h992pvsy8hvvyqyquur',
            'num_of_days' => 4,
            'format'      => 'json',
            'q'           => $search
        ]);

        $curl = curl_init('http://api.worldweatheronline.com/free/v1/weather.ashx?' . $get_params);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if (! $result = curl_exec($curl)) {
            return false;
        }

        $result = json_decode($result);
        $result = $result->data;

        curl_close($curl);

        if (isset($result->error)) {
            $data = ['error' => $result->error[0]->msg];
        } else {
            // Let's normalize the data a bit
            $data = [
                'location' => [
                    'name' => static::normalize_weather_location($search, $result->request[0]->query),
                    'type' => $result->request[0]->type
                ],
                'current' => self::normalize_weather($result->current_condition[0], true),
                'forcast' => [],
            ];

            foreach ($result->weather as $forcast) {
                $data['forcast'][] = self::normalize_weather($forcast);
            }
        }

        // We'll cache the info for future requests
        set_transient($trans_key, $data, 60 * 60);

        return $data;
    }

    protected static function normalize_weather_location($search, $location)
    {
        $map = include __DIR__.'/zip-codes.php';

        if (isset($map[$search])) {
            $location = $map[$search];
        }

        unset($map);

        return $location;
    }

    // 2. Get currency info
    public static function get_currency_info($date = null)
    {
        $appId = '?app_id=' . self::$currency_exchange_app_id;

        if (! $date) {
            $filename = 'latest.json';
        } elseif (preg_match('/\d{4}-\d{2}-\d{2}/', $date)) {
            $filename = "historical/$date.json";
        } else {
            return false;
        }

        $trans_key = 'stocks_' . $filename;

        // First we check the cache
        if ($data = get_transient($trans_key)) {
            return $data;
        }

        $data = [];

        // No cache? Let's get a list of the currencies available
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL             => 'http://openexchangerates.org/currencies.json' . $appId,
            CURLOPT_RETURNTRANSFER  => true
        ]);

        $result = json_decode(curl_exec($curl), 1);

        curl_close($curl);

        $data['currencies'] = $result;

        // then we'll get the actual exchange rates
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL              => 'http://openexchangerates.org/' . $filename . $appId,
            CURLOPT_RETURNTRANSFER   => true
        ]);

        $result = json_decode(curl_exec($curl), 1);

        curl_close($curl);

        $data['timestamp'] = $result['timestamp'];

        $data['rates'] = $result['rates'];

        // We don't need this much precision...
        foreach ($data['rates'] as &$rate) {
            $rate = substr($rate, 0, 6);
        }

        // and we'll cache it...
        set_transient($trans_key, $data, 60 * 60 * 4);

        return $data;
    }

    // 3. Get stocks info
    public static function get_stocks_info($symbols = ['^IXIC'])
    {
        $key = 'stocks_' . implode('', $symbols);

        // First we check the cache
        if ($stocks = get_transient($key)) {
            return $stocks;
        }

        $stocks = [];

        $columns = implode('', [
            's',  // Symbol
            'l1', // Last Trade (Price Only)
            'c',  // Change
            'n',  // Name
            'r'   // P/E Ratio
        ]);

        $symbols = implode('+', $symbols);

        $h = fopen('http://finance.yahoo.com/d/quotes.csv?s=' . $symbols . '&f=' . $columns, 'r');

        while ($row = fgetcsv($h)) {
            if (is_null($row[0]) || $row[1] == 'N/A') {
                continue;
            }

            $change = explode(' - ', $row[2]);

            $stocks[] = [
                'symbol'         => $row[0],
                'name'           => $row[3],
                'price'          => $row[1],
                'pe'             => $row[4],
                'change_points'  => $change[0],
                'change_percent' => $change[1],
            ];
        }

        $stocks = self::normalize_stock_symbols($stocks);

        // and we'll cache it...
        set_transient($key, $stocks, 60 * 20);

        return $stocks;
    }

    // 4. Get hebrew date
    public static function get_hebrew_date($date = false)
    {
        // Default date is now
        $date = $date ? $date : time();

        $julian = gregoriantojd(
            date('m', $date),
            date('d', $date),
            date('Y', $date)
        );

        // Check the cache
        if (array_key_exists($julian, self::$hebrew_dates_cache)) {
            return self::$hebrew_dates_cache[$julian];
        }

        $hebrew = jdtojewish(
            $julian,
            true, // We want it in hebrew characters
            CAL_JEWISH_ADD_ALAFIM_GERESH + CAL_JEWISH_ADD_GERESHAYIM
        );

        // Convert to utf-8
        $hebrew = iconv('WINDOWS-1255', 'UTF-8', $hebrew);

        // Remove the ALAFIM part
        $hebrew = preg_replace("/ ה'/", ' ', $hebrew);

        // Turn spaces into &nbsp; so that they don't break on a line break
        $hebrew = preg_replace('/ /', '&nbsp;', $hebrew);

        // Cache the date
        self::$hebrew_dates_cache[$julian] = $hebrew;

        return $hebrew;
    }



    protected static function get_xml_attr($array, $val)
    {
        return $array[$val]['@attributes']['data'];
    }

    protected static function normalize_weather($weather, $is_current_condition = false)
    {
        // Get icon file name
        preg_match('/[a-z][a-z_]+\.png$/i', $weather->weatherIconUrl[0]->value, $icon);

        $out = [
            'condition' => $weather->weatherDesc[0]->value,
            'icon'      => $icon[0],
        ];

        if ($is_current_condition) {
            $out['temp_f'] = $weather->temp_F;
            $out['temp_c'] = $weather->temp_C;
        } else {
            $out['tempMaxC'] = $weather->tempMaxC;
            $out['tempMaxF'] = $weather->tempMaxF;
            $out['tempMinC'] = $weather->tempMinC;
            $out['tempMinF'] = $weather->tempMinF;

            // Get day of the week
            $date = DateTime::createFromFormat('Y-m-d', $weather->date);
            $out['day_of_week'] = date('D', $date->getTimestamp());
        }

        return $out;
    }

    protected static function normalize_stock_symbols($stocks)
    {
        $aliases = [
            '^DJI'  => 'Dow',
            '^IXIC' => 'NASDAQ',
            '^GSPC' => 'S&P 500',
        ];

        foreach ($stocks as &$stock) {
            if (isset($aliases[ $stock['symbol'] ])) {
                $stock['symbol'] = $aliases[$stock['symbol']];
            }
        }

        return $stocks;
    }
}
