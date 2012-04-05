    <div class="ci_col-5of6">
        <div class=" admin_box">
        <h1 class="admin_box_title">Edit Todo Item</h1>
        <a href="/todo/">Return to ToDo screen</a>
<fieldset>
<form method="post" action="/todo/">
<input type="hidden" name="flag" value="EDIT" />
<input type="hidden" name="id" value="<?=$item['id'];?>" />
<label for="title">Title:</label><br/>
<input type="text" name="title" maxlength="255" size="50" value="<?=$item['title'];?>"/><br/>
<label for="status">Status:</label><br/>
<select name="status">
<?php
foreach ($status as $option){
  if ($item['status'] != $option['id']){
    $selected = "";
  }else{
    $selected = "selected";
  }
  echo "<option value=\"{$option['id']}\" $selected>{$option['description']}</option>";
}
?>
</select><br/>
<label for="priority">Priority:</label><br/>
<select name="priority">
<?php
foreach ($priority as $option){
  if ($item['priority'] != $option['id']){
    $selected = "";
  }else{
    $selected = "selected";
  }
  echo "<option value=\"{$option['id']}\" $selected>{$option['description']}</option>";
}
?>
</select><br/>
<label for="Notes">Notes:</label><br/>
<textarea name="notes"><?=$item['notes'];?></textarea><br/>
<input type="submit" name="submit" value="Submit" />
<input type="button" value="Cancel" onclick="window.location.href='/todo'">
</form>
</fieldset>

        </div>
    </div>