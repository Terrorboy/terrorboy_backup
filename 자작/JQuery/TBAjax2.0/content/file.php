<?php
echo "실제파일 위치: ./ajax/content/file.php<br>";

echo "POST<br>";
print_r($_POST);
echo "<br><br>";


echo "FILES<br>";
print_r($_FILES);
unset($_FILES['MyFile']['tmp_name']);