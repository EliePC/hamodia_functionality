<style>
.story-updates-item {
    display: flex;
    align-items: center;
    margin-bottom: 4px;
}
.story-updates-label {
    flex-basis: 180px;
}
.story-updates-textarea {
    flex: 1;
}
.story-update-delete {
    margin-left: 20px !important;
}
</style>

<div class="story-updates-wrapper">

    <?php foreach ($updates as $date => $update): ?>
    <div class="story-updates-item">
        <b class="story-updates-label">
            On <?= (new DateTime($date))->setTimezone(
                        new DateTimeZone('America/New_York')
                   )->format('m/d/Y h:i a') ?>
            :
        </b>
        <textarea class="story-updates-textarea"
                  name="<?= $key ?>[<?= $date ?>]"
                  placeholder="Short description of what was updated"
                  cols="30"
                  rows="1"
        ><?= htmlspecialchars($update) ?></textarea>
        <button type="button" class="button story-update-delete">Delete</button>
    </div>
    <?php endforeach; ?>

    <?php if (count($updates)): ?>
        <hr>
    <?php endif; ?>

    <div class="story-updates-item">
        <b class="story-updates-label">New update: </b>
        <textarea class="story-updates-textarea"
                  name="<?= $key ?>[now] ?>"
                  placeholder="Short description of what was updated"
                  cols="30"
                  rows="1"
        ></textarea>
    </div>
</div>

<script>
jQuery('.story-updates-wrapper').on('click', '.story-update-delete', function() {
    jQuery(this).closest('.story-updates-item').remove();
});
</script>
