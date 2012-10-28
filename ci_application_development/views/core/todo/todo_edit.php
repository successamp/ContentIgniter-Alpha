<div id="Content" class="ci_col-1of1 clearfix ci_bottom">
    <div class="ui-widget ui-widget-content ui-corner-all clearfix">
        <h1 class="ui-widget-header ci-admin-header" id="PageTitle">Edit Todo Item
            <small>
                <button href="/todo/">Return to ToDo</button>
            </small>
        </h1>
        <fieldset>
            <form method="post" action="/todo/">
                <input type="hidden" name="flag" value="EDIT"/>
                <input type="hidden" name="id" value="<?=$item['id'];?>"/>
                <label for="title">Title:</label><br/>
                <input type="text" name="title" maxlength="255" size="50" value="<?=$item['title'];?>"/><br/>
                <label for="status">Status:</label><br/>
                <select name="status">
                    <?php
                    foreach ($status as $option) {
                        if ($item['status'] != $option['id']) {
                            $selected = "";
                        } else {
                            $selected = "selected";
                        }
                        echo "<option value=\"{$option['id']}\" $selected>{$option['description']}</option>";
                    }
                    ?>
                </select><br/>
                <label for="priority">Priority:</label><br/>
                <select name="priority">
                    <?php
                    foreach ($priority as $option) {
                        if ($item['priority'] != $option['id']) {
                            $selected = "";
                        } else {
                            $selected = "selected";
                        }
                        echo "<option value=\"{$option['id']}\" $selected>{$option['description']}</option>";
                    }
                    ?>
                </select><br/>
                <label for="Notes">Notes:</label><br/>
                <textarea name="notes"><?=$item['notes'];?></textarea><br/>
                <input type="submit" name="submit" value="Submit"/>
                <input type="button" value="Cancel" onclick="window.location.href='/todo'">
            </form>
        </fieldset>

    </div>
</div>