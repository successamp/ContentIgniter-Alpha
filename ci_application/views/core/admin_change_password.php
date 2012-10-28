<style type="text/css">

</style>
<div id="Content" class="ci_col-1of1 clearfix ci_bottom">
    <div class="ui-widget ui-widget-content ui-corner-all clearfix">
        <h1 class="ui-widget-header ci-admin-header" id="PageTitle">Change Password</h1>
        <?php
        echo empty($msg) ? '' : '<br><div class="msg ' . $msg['class'] . '">' . $msg['text'] . '</div>';
        ?>

        <br>

        <form class="simpleForm" method="post" action="/admin/change_password/">
            <label for="start">User:</label><select id="username" name="username">
            <?php
            foreach ($users as $u) {
                echo '<option value="' . $u['username'] . '">' . $u['username'] . '</option>';
            }
            ?>
            <option value=""></option>
        </select>
            <label for="end">Password:</label><input type="password" id="password" name="password">
            <input type="submit" value="Change Password">
        </form>

    </div>
</div>