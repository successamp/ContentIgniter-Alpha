<div id="Content" class="ci_col-1of1 clearfix ci_bottom">
    <div class="ui-widget ui-widget-content ui-corner-all clearfix">
        <h1 class="ui-widget-header ci-admin-header" id="PageTitle">Admin ToDo</h1>

        <div class="ci_col-3of5">
            <h1>Active Todo List:</h1>
            <?php
            foreach ($active_list as $item) {
                if ($item['priority'] == 1) {
                    echo "<div class=\"low\" rel=\"{$item['id']}\">";
                } elseif ($item['priority'] == 2) {
                    echo "<div class=\"medium\" rel=\"{$item['id']}\">";
                } elseif ($item['priority'] == 3) {
                    echo "<div class=\"high\" rel=\"{$item['id']}\">";
                } elseif ($item['priority'] == 4) {
                    echo "<div class=\"critical\" rel=\"{$item['id']}\">";
                }
                echo "<h3>{$item['title']}</h3>\n";
                echo "<span class=\"editFlag\"><a href=\"/todo/edit/{$item['id']}\">edit</a></span>\n";
                echo "<p>{$item['notes']}</p>";
                echo "</div>\n";
            }
            ?>
        </div>
        <div class="ci_col-2of5">
            <h1>Waiting List:</h1>
            <ul class="waiting">
                <?php
                foreach ($waiting_list as $item) {
                    if ($item['priority'] == 1) {
                        echo "<li><strong class=\"low\">";
                    } elseif ($item['priority'] == 2) {
                        echo "<li><strong class=\"medium\">";
                    } elseif ($item['priority'] == 3) {
                        echo "<li><strong class=\"high\">";
                    } elseif ($item['priority'] == 4) {
                        echo "<li><strong class=\"critical\">";
                    }
                    echo $item['title'] . "</strong> - \n" . $item['notes'] . " - <a href=\"/todo/edit/{$item['id']}\">edit</a></li>\n";
                }
                ?>
            </ul>
        </div>
        <div class="ci_col-1of1">
            <fieldset>
                <form method="post" action="/todo/">
                    <input type="hidden" name="flag" value="ADD"/>
                    <label for="title">Title:</label><br/>
                    <input type="text" name="title" maxlength="255" size="50"/><br/>
                    <label for="status">Status:</label><br/>
                    <select name="status">
                        <?php
                        foreach ($status as $option) {
                            if ($option['id'] != 0) {
                                echo "<option value=\"{$option['id']}\">{$option['description']}</option>";
                            }
                        }
                        ?>
                    </select><br/>
                    <label for="priority">Priority:</label><br/>
                    <select name="priority">
                        <?php
                        foreach ($priority as $option) {
                            echo "<option value=\"{$option['id']}\">{$option['description']}</option>";
                        }
                        ?>
                    </select><br/>
                    <label for="Notes">Notes:</label><br/>
                    <textarea name="notes"></textarea><br/>
                    <input type="submit" name="submit" value="Submit"/>
                </form>
            </fieldset>
        </div>
    </div>
</div>

