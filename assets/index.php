<?php
const ENCRYPTION_TYPE= [
    "た",
    "た！",
    "た...",
];

const ENCRYPTION_STRENGTH = [
    10,//%
    30,//%
    50,//%
];


class LexicalAnalysisTanuki
{
    protected $pos_i;
    protected $max_text_i;
    protected $text;
    protected $tanuki_tokens;
    protected $encryption_lex;
    protected $encryption_lex_word_count
    __construct($text, $encryption_lex) {
        $this->pos_i = 0;
        $this->max_text_i = mb_strlen($text) - 1;
        $this->text = $text;
        $this->tanuki_tokens = [];
        $this->encryption_lex = $encryption_lex;
        $this->encryption_lex_word_count = mb_strlen($encryption_lex) - 1;
        $this->lexicalAnalysisExecute();
    }
    function lexicalAnalysisExecute() {

        $this->getChar();
    }
    function getChar() {
        $is_out_of_range = $pos_i >= max_text_i
        if ($is_out_of_range) {
            return "";
        } else {
            return  mb_substr($input_text, $this->pos_i, 1);
        }
    }
}


function  lexicalAnalysisTanuki ($input_text, $encryption_string) {
    $tanuli_tokens = [];
    $input_text_word_count = mb_strlen($input_text);
    for ($i=0; $i < $input_text_word_count; $i++) {
    }

    retun $tanuli_tokens;
}

function getChar($pos, $string) {
    $input_text_word_count = mb_strlen($input_text);

    if ()
    $char =

    return
}

function encryptionString ($pos, $string) {
    $char = mb_substr($input_text, $i, 1);
    $next_char = $char = mb_substr($input_text, $i + 1, 1);
    $char =
    if ()
}


require_once "util.inc.php";
$is_server_request_post = $_SERVER["REQUEST_METHOD"] === "POST";
$input_text             = $_POST["input_text"];
$is_encryption          = $is_server_request_post && ($_POST["encryption"]  === "暗号化");
$is_composite           = $is_server_request_post && ($_POST["composite"]   === "複合化");
$encryption_type        = $_POST["encryption_type"];
$encryption_strength    = $_POST["encryption_strength"];

if ($is_encryption) {
    $input_text_word_count = mb_strlen($input_text);
    $encryption_string = ENCRYPTION_STRING[$encryption_type];
    $encryption_string_appear_rato = ENCRYPTION_STRENGTH[$encryption_strength];
    for ($i=0; $i < $input_text_word_count; $i++) {
        //echo "<p>" . mb_substr($input_text, $i, 1) . "</p>";

    }
} elseif (is_composite) {

}


?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>たぬき暗号</title>
</head>
<body>
    <h1>たぬき暗号</h1>
    <form action="" method="POST">
        <p>暗号の種類を選択します。</p>
        <p>
            <select name="encryption_type">
                <option value="0" selected>ふつうのたぬき</option>
                <option value="1">元気なたぬき</option>
                <option value="2">疲れたたぬき</option>
            </select>
        </p>
        <p>暗号の強度を選択します。</p>
        <p>
            <select name="encryption_strength">
                <option value="0" selected>ふつう</option>
                <option value="1">つよい</option>
                <option value="2">すごくつよい</option>
            </select>
        </p>
        <p>
            <textarea name="input_text" cols="30" rows="10" value = "<?= h($input_text); ?>"></textarea>
            <input type="submit" name="encryption" value="暗号化">
            <input type="submit" name="composite" value="複合化">
            <textarea name="output_text" cols="30" rows="10" value = "<?= h($output_text); ?>"></textarea>
        </p>
    </form>
</body>
</html>