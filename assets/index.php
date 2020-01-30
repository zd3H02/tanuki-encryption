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

class LexicalAnalyzer
{
    const TOKEN_TYPE = "token_type";
    const TOKEN = "token";
    const TOKEN_TYPE_ENCRYPTION = "encryption";
    const TOKEN_TYPE_NORMAL = "normal";
    const TOKEN_TYPE_NORMAL_NEXT_ENCRYPTION = "next_encryption";
    const TOKEN_TYPE_NORMAL_PREV_ENCRYPTION = "prev_encryption";
    const TOKEN_TYPE_NORMAL_WHILE_ENCRYPTION = "while_encryption";
    const TOKEN_TYPE_NONE = "none";
    function __construct($string_to_lecical_analyze, $reserved_word_encryption, $reserved_word_encryption_appear_rato) {
        $this->now_char_pos_i = 0;
        $this->max_char_pos_i = mb_strlen($string_to_lecical_analyze) - 1;
        $this->string_to_lecical_analyze = $string_to_lecical_analyze;
        $this->temp_analyzed_tokens = [];
        $this->analyzed_tokens = [];
        $this->reserved_word_encryption = $reserved_word_encryption;
        $this->count_of_reserved_word_encryption = mb_strlen($reserved_word_encryption);
        $this->reserved_word_encryption_appear_rato = $reserved_word_encryption_appear_rato;
        $this->lexicalAnalyzerExecute();
        $this->lexicalAnalyzerBreakDownExecute();

        //echo "<p>--string_to_lecical_analyze-------------</p>";
        var_dump($this->now_char_pos_i);
        var_dump($this->max_char_pos_i);
        var_dump($this->string_to_lecical_analyze);
        var_dump($this->temp_analyzed_tokens);
        var_dump($this->analyzed_tokens);
        var_dump($this->reserved_word_encryption);
        var_dump($this->count_of_reserved_word_encryption);
    }
    function getChar($char_pos_i) {
        $is_out_of_range = $this->now_char_pos_i > $this->max_char_pos_i;
        if ($is_out_of_range) {
            return "";
        } else {
            $get_char = mb_substr($this->string_to_lecical_analyze, $char_pos_i, 1);
            return $get_char;
        }
    }
    function seekEncryptionToken() {
        $get_chars = "";
        for ($get_char_i = 0; $get_char_i < $this->count_of_reserved_word_encryption; $get_char_i++) {
            $next_get_char_pos_i = $get_char_i + $this->now_char_pos_i;
            $get_chars = $get_chars . $this->getChar($next_get_char_pos_i);
        }
        $is_reserved_word_encryption = $get_chars === $this->reserved_word_encryption;
        if ($is_reserved_word_encryption) {
            $this->now_char_pos_i += $this->count_of_reserved_word_encryption;
            return $get_chars . $this->seekEncryptionToken();
        } else {
            return "";
        }
    }
    function tokenEncryption() {
        $get_chars = $this->seekEncryptionToken();
        $is_reserved_word_encryption_exists = $get_chars !== "";
        if ($is_reserved_word_encryption_exists) {
            $this->temp_analyzed_tokens[] = [
                $this::TOKEN_TYPE   => $this::TOKEN_TYPE_ENCRYPTION,
                $this::TOKEN        => $get_chars,
            ];
        }
    }
    function tokenNormal() {
        $this->temp_analyzed_tokens[] = [
            $this::TOKEN_TYPE   => $this::TOKEN_TYPE_NORMAL,
            $this::TOKEN        => $this->getChar($this->now_char_pos_i),
        ];
        $this->now_char_pos_i++;
    }
    function tokenTypeBreakDown($token_i) {
        $token_type = $this->temp_analyzed_tokens[$token_i][$this::TOKEN_TYPE];
        echo $token_type . "----";
        $is_token_type_normal = $token_type === $this::TOKEN_TYPE_NORMAL;
        $is_tokens = count($this->temp_analyzed_tokens) > 1;
        $is_normal_break_down_execute = $is_token_type_normal && $is_tokens;
        if ($is_normal_break_down_execute) {
            $is_first_token = $token_i === 0;
            $is_last_token = $token_i === count($this->temp_analyzed_tokens) - 1;
            if ($is_first_token) {
                $next_token_i = $token_i + 1;
                $prev_token_type = $this::TOKEN_TYPE_NONE;
                $next_token_type = $this->temp_analyzed_tokens[$next_token_i][$this::TOKEN_TYPE];
            } elseif ($is_last_token) {
                $prev_token_i = $token_i - 1;
                $prev_token_type = $this->temp_analyzed_tokens[$prev_token_i][$this::TOKEN_TYPE];
                $next_token_type = $this::TOKEN_TYPE_NONE;
            } else {
                $prev_token_i = $token_i - 1;
                $next_token_i = $token_i + 1;
                $prev_token_type = $this->temp_analyzed_tokens[$prev_token_i][$this::TOKEN_TYPE];
                $next_token_type = $this->temp_analyzed_tokens[$next_token_i][$this::TOKEN_TYPE];
            }
            $is_token_type_prev     = $prev_token_type === $this::TOKEN_TYPE_ENCRYPTION;
            $is_token_type_next     = $next_token_type === $this::TOKEN_TYPE_ENCRYPTION;
            $is_token_type_while    = $is_token_type_prev && $is_token_type_next;
            if ($is_token_type_while ) {
                return $this::TOKEN_TYPE_NORMAL_WHILE_ENCRYPTION;
            } elseif ($is_token_type_prev) {
                return $this::TOKEN_TYPE_NORMAL_PREV_ENCRYPTION;
            } elseif ($is_token_type_next) {
                return $this::TOKEN_TYPE_NORMAL_NEXT_ENCRYPTION;
            } else {
                return $this::TOKEN_TYPE_NORMAL;
            }
        } else {
            return $token_type;
        }
    }
    function lexicalAnalyzerBreakDownExecute() {
        for ($token_i = 0; $token_i < count($this->temp_analyzed_tokens); $token_i++) {
            $this->analyzed_tokens[] = [
                $this::TOKEN_TYPE   => $this->tokenTypeBreakDown($token_i),
                $this::TOKEN        => $this->temp_analyzed_tokens[$token_i][$this::TOKEN],
            ];
        }
    }
    function lexicalAnalyzerExecute() {
        $this->tokenEncryption();
        $this->tokenNormal();
        if ($this->now_char_pos_i <= $this->max_char_pos_i) {
            $this->lexicalAnalyzerExecute();
        }
    }
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
    $reserved_word_encryption = ENCRYPTION_TYPE[$encryption_type];
    $reserved_word_encryption_appear_rato = ENCRYPTION_STRENGTH[$encryption_strength];
    $test = new LexicalAnalyzer($input_text, $reserved_word_encryption, $reserved_word_encryption_appear_rato);

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