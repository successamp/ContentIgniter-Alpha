<style type="text/css">

</style>
    <div class="ci_col-5of6">
        <div class="admin_box" style="padding:0;">
            <h1 class="admin_box_title">Change Password</h1>
<?php
    echo empty($msg) ? '' : '<br><div class="msg '.$msg['class'].'">'.$msg['text'].'</div>';
?>

            <br>
            <form class="simpleForm" method="post" action="/admin/change_password/">
                <label for="start">User:</label><select id="username" name="username">
<?php
foreach($users as $u){
    echo '<option value="'.$u['username'].'">'.$u['username'].'</option>';
}
?>
                    <option value=""></option>
                </select>
                <label for="end">Password:</label><input type="password" id="password" name="password">
                <input type="submit" value="Change Password">
            </form>

        </div>
    </div>