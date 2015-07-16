<style>
    .textinput {
        width: 400px;
    }
</style>

<script>
    function toArray(arrayLike) {
        return Array.prototype.slice.call(arrayLike);
    }

    function clearSiblingInputs(evt) {
        var target = evt.currentTarget || evt.target;

        if (!target) {
            return;
        }

        evt.preventDefault();

        toArray(target.parentNode.querySelectorAll("input")).
        forEach(function (input) {
            input.checked = false;
        });
    }

    toArray(document.querySelectorAll("[data-action=clear]")).
    forEach(function (el) {
        el.addEventListener("click", clearSiblingInputs);
    });
</script>

<form action="options.php" method="post">
    <?php
    if (array_key_exists("page_title", $settings)) {
        printf("<h2>%s</h2>", $settings["page_title"]);
    }

    if (array_key_exists("description", $settings)) {
        printf("<p>%s</p>", $settings["description"]);
    }

    settings_fields($settings["setting_name"]);
    do_settings_sections($settings["page_name"]);
    submit_button();
    ?>
</form>
