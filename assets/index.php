<?php
const ENCRYPTION_TYPE_SETS= [
    ["ふつうのたぬき"=>"た"],
    ["元気なたぬき"=>"た！"],
    ["つかれたぬき"=>"た..."],
];


const ENCRYPTION_STRENGTH = [
    10,//%
    30,//%
    50,//%
];

class LexicalAnalyzer
{
    const KEY_TOKEN_TYPE = "token_type";
    const KEY_TOKEN_CONTENT = "token_content";
    const TOKEN_TYPE_ENCRYPTION = "encryption";
    const TOKEN_TYPE_NORMAL = "normal";
    const TOKEN_TYPE_NEXT_ENCRYPTION = "next_encryption";
    const TOKEN_TYPE_PREV_ENCRYPTION = "prev_encryption";
    const TOKEN_TYPE_WHILE_ENCRYPTION = "while_encryption";
    const TOKEN_TYPE_NONE = "none";

    protected $now_char_pos_i;
    protected $max_char_pos_i;
    protected $string_to_lecical_analyze;
    protected $temp_analyzed_tokens;
    protected $analyzed_tokens;
    protected $encryption_type;
    protected $reserved_encryption_word;
    protected $reserved_encryption_word_len;

    function __construct($string_to_lecical_analyze, $encryption_type_set) {
        $this->now_char_pos_i = 0;
        $this->max_char_pos_i = mb_strlen($string_to_lecical_analyze) - 1;
        $this->string_to_lecical_analyze = $string_to_lecical_analyze;
        $this->temp_analyzed_tokens = [];
        $this->analyzed_tokens = [];
        $encryption_type_set_keys = array_keys($encryption_type_set);
        $this->encryption_type = $encryption_type_set_keys[0];
        $this->reserved_encryption_word = $encryption_type_set[$this->encryption_type];
        $this->reserved_encryption_word_len = mb_strlen($this->reserved_encryption_word);

        $this->lexicalAnalyzerExecute();
        $this->tokenTypeBreakDownExecute();

        // var_dump($this->now_char_pos_i);
        // var_dump($this->max_char_pos_i);
        // var_dump($this->reserved_encryption_word);
        // var_dump($this->string_to_lecical_analyze);
        // var_dump($this->temp_analyzed_tokens);
        // var_dump($this->analyzed_tokens);
        // var_dump($this->reserved_encryption_word);
        // var_dump($this->reserved_encryption_word_len);
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
        for ($get_char_i = 0; $get_char_i < $this->reserved_encryption_word_len; $get_char_i++) {
            $next_get_char_pos_i = $get_char_i + $this->now_char_pos_i;
            $get_chars = $get_chars . $this->getChar($next_get_char_pos_i);
        }
        $is_reserved_encryption_word = $get_chars === $this->reserved_encryption_word;
        if ($is_reserved_encryption_word) {
            $this->now_char_pos_i += $this->reserved_encryption_word_len;
            return $get_chars . $this->seekEncryptionToken();
        } else {
            return "";
        }
    }
    function tokenEncryption() {
        $get_chars = $this->seekEncryptionToken();
        $is_reserved_encryption_word_exists = $get_chars !== "";
        if ($is_reserved_encryption_word_exists) {
            $this->temp_analyzed_tokens[] = [
                $this::KEY_TOKEN_TYPE       => $this::TOKEN_TYPE_ENCRYPTION,
                $this::KEY_TOKEN_CONTENT    => $get_chars,
            ];
        }
    }
    function tokenNormal() {
        $this->temp_analyzed_tokens[] = [
            $this::KEY_TOKEN_TYPE       => $this::TOKEN_TYPE_NORMAL,
            $this::KEY_TOKEN_CONTENT    => $this->getChar($this->now_char_pos_i),
        ];
        $this->now_char_pos_i++;
    }
    function tokenTypeBreakDown($token_i) {
        $token_type = $this->temp_analyzed_tokens[$token_i][$this::KEY_TOKEN_TYPE];
        $is_token_type_normal = $token_type === $this::TOKEN_TYPE_NORMAL;
        $is_tokens = count($this->temp_analyzed_tokens) > 1;
        $is_normal_break_down_execute = $is_token_type_normal && $is_tokens;
        if ($is_normal_break_down_execute) {
            $is_first_token = $token_i === 0;
            $is_last_token = $token_i === count($this->temp_analyzed_tokens) - 1;
            if ($is_first_token) {
                $next_token_i = $token_i + 1;
                $prev_token_type = $this::TOKEN_TYPE_NONE;
                $next_token_type = $this->temp_analyzed_tokens[$next_token_i][$this::KEY_TOKEN_TYPE];
                $analyzed_prev_token_type = $this::TOKEN_TYPE_NONE;
                $analyzed_next_token_type = $this->analyzed_tokens[$next_token_i][$this::KEY_TOKEN_TYPE];
            } elseif ($is_last_token) {
                $prev_token_i = $token_i - 1;
                $prev_token_type = $this->temp_analyzed_tokens[$prev_token_i][$this::KEY_TOKEN_TYPE];
                $next_token_type = $this::TOKEN_TYPE_NONE;
                $analyzed_prev_token_type = $this->analyzed_tokens[$prev_token_i][$this::KEY_TOKEN_TYPE];
                $analyzed_next_token_type = $this::TOKEN_TYPE_NONE;
            } else {
                $prev_token_i = $token_i - 1;
                $next_token_i = $token_i + 1;
                $prev_token_type = $this->temp_analyzed_tokens[$prev_token_i][$this::KEY_TOKEN_TYPE];
                $next_token_type = $this->temp_analyzed_tokens[$next_token_i][$this::KEY_TOKEN_TYPE];
                $analyzed_prev_token_type = $this->analyzed_tokens[$prev_token_i][$this::KEY_TOKEN_TYPE];
                $analyzed_next_token_type = $this->analyzed_tokens[$next_token_i][$this::KEY_TOKEN_TYPE];
            }
            $is_token_type_prev = $prev_token_type === $this::TOKEN_TYPE_ENCRYPTION;
            $is_token_type_next = $next_token_type === $this::TOKEN_TYPE_ENCRYPTION;
            $is_analyzed_token_type_prev = $analyzed_prev_token_type === $this::TOKEN_TYPE_PREV_ENCRYPTION;
            $is_token_type_while =
                    ($is_token_type_prev && $is_token_type_next)
                ||  ($is_token_type_next && $is_analyzed_token_type_prev)
                ;
            if ($is_token_type_while) {
                return $this::TOKEN_TYPE_WHILE_ENCRYPTION;
            } elseif ($is_token_type_prev) {
                return $this::TOKEN_TYPE_PREV_ENCRYPTION;
            } elseif ($is_token_type_next) {
                return $this::TOKEN_TYPE_NEXT_ENCRYPTION;
            } else {
                return $this::TOKEN_TYPE_NORMAL;
            }
        } else {
            return $token_type;
        }
    }
    function lexicalAnalyzerExecute() {
        while($this->now_char_pos_i <= $this->max_char_pos_i) {
            $is_tokenEncryption_full = $this->tokenEncryption();
            if ($is_tokenEncryption_full) continue;
            $this->tokenNormal();
        }
    }
    function tokenTypeBreakDownExecute() {
        for ($token_i = 0; $token_i < count($this->temp_analyzed_tokens); $token_i++) {
            $this->analyzed_tokens[] = [
                $this::KEY_TOKEN_TYPE       => $this->tokenTypeBreakDown($token_i),
                $this::KEY_TOKEN_CONTENT    => $this->temp_analyzed_tokens[$token_i][$this::KEY_TOKEN_CONTENT],
            ];
        }
    }
}

class TanukiEncryptionGenerator extends LexicalAnalyzer
{
    protected $max_random_draw_num;
    protected $tanuki_encryption;
    function __construct($string_to_lecical_analyze, $encryption_type_set, $reserved_encryption_word_appearance_rato) {
        parent::__construct($string_to_lecical_analyze, $encryption_type_set);

        $reserved_encryption_word_appearance_num = ceil(mb_strlen($string_to_lecical_analyze) * $reserved_encryption_word_appearance_rato / 100);
        $can_be_attached_token_num = 0;
        foreach ($this->analyzed_tokens as $token) {
            $is_can_be_attached_token =
                    $token[$this::KEY_TOKEN_TYPE] === $this::TOKEN_TYPE_NORMAL
                ||  $token[$this::KEY_TOKEN_TYPE] === $this::TOKEN_TYPE_NEXT_ENCRYPTION
                ||  $token[$this::KEY_TOKEN_TYPE] === $this::TOKEN_TYPE_PREV_ENCRYPTION;
            if($is_can_be_attached_token) {
                $can_be_attached_token_num++;
            }
        }
        $is_reserved_encryption_word_appearance_num_too_big = $reserved_encryption_word_appearance_num > $can_be_attached_token_num;
        if ($is_reserved_encryption_word_appearance_num_too_big) {
            $this->max_random_draw_num = $can_be_attached_token_num;
        } else {
            $this->max_random_draw_num = $reserved_encryption_word_appearance_num;
        }

        $this->tanuki_encryption = [];

        $this->generateTanukiEncryption();
    }
    function getRandamSelectAttachedTokens() {
        $shuffled_analyzed_tokens = [];
        foreach ($this->analyzed_tokens as $i => $token) {
            $is_can_be_attached_token =
                    $token[$this::KEY_TOKEN_TYPE] === $this::TOKEN_TYPE_NORMAL
                ||  $token[$this::KEY_TOKEN_TYPE] === $this::TOKEN_TYPE_NEXT_ENCRYPTION
                ||  $token[$this::KEY_TOKEN_TYPE] === $this::TOKEN_TYPE_PREV_ENCRYPTION;
            if ($is_can_be_attached_token) {
                $shuffled_analyzed_tokens[] = [
                    "order" =>$i,
                    "token" =>$token,
                ];
            }
        }
        shuffle($shuffled_analyzed_tokens);
        $select_to_be_attached_tokens = [];
        for ($draw_i = 0; $draw_i < $this->max_random_draw_num; $draw_i++) {
            $analyzed_tokens_i = $shuffled_analyzed_tokens[$draw_i]["order"];
            $select_to_be_attached_tokens[$analyzed_tokens_i] = $this->analyzed_tokens[$analyzed_tokens_i];
        }
        return $select_to_be_attached_tokens;
    }
    function generateTanukiEncryption() {
        $randam_select_attached_tokens = $this->getRandamSelectAttachedTokens();
        foreach ($this->analyzed_tokens as $analyzed_tokens_i => $token) {
            $is_randam_select_token = array_key_exists($analyzed_tokens_i, $randam_select_attached_tokens);
            $is_token_type_encryption = $token[$this::KEY_TOKEN_TYPE] === $this::TOKEN_TYPE_ENCRYPTION;
            $is_reserved_encryption_word_attache = $is_randam_select_token || $is_token_type_encryption;
            if ($is_reserved_encryption_word_attache) {
                $is_prev = $token[$this::KEY_TOKEN_TYPE] === $this::TOKEN_TYPE_PREV_ENCRYPTION;
                if ($is_prev) {
                    $this->tanuki_encryption[$analyzed_tokens_i][$this::KEY_TOKEN_TYPE]
                        = $this->analyzed_tokens[$analyzed_tokens_i][$this::KEY_TOKEN_TYPE];
                    $this->tanuki_encryption[$analyzed_tokens_i][$this::KEY_TOKEN_CONTENT]
                        =  $this->analyzed_tokens[$analyzed_tokens_i][$this::KEY_TOKEN_CONTENT] . $this->reserved_encryption_word;
                } else {
                    $this->tanuki_encryption[$analyzed_tokens_i][$this::KEY_TOKEN_TYPE]
                        = $this->analyzed_tokens[$analyzed_tokens_i][$this::KEY_TOKEN_TYPE];
                    $this->tanuki_encryption[$analyzed_tokens_i][$this::KEY_TOKEN_CONTENT]
                        = $this->reserved_encryption_word . $this->analyzed_tokens[$analyzed_tokens_i][$this::KEY_TOKEN_CONTENT];
                }
            } else {
                $this->tanuki_encryption[$analyzed_tokens_i] = $this->analyzed_tokens[$analyzed_tokens_i];
            }
        }
    }
    function getTanukiEncryption() {
        $tanuki_encryption = "";
        for($i = 0; $i < count($this->tanuki_encryption); $i++) {
            $content = $this->tanuki_encryption[$i][$this::KEY_TOKEN_CONTENT];
            $tanuki_encryption =  $tanuki_encryption . $content;
        }
        $tanuki_encryption = $tanuki_encryption . "\n" .$this->encryption_type;
        return $tanuki_encryption;
    }
}

class TanukiEncryptionDecoder extends LexicalAnalyzer
{
    function __construct($string_to_lecical_analyze) {
        $encryption_type_set = [];
        $i = 0;
        $max_i = count(ENCRYPTION_TYPE_SETS);
        foreach (ENCRYPTION_TYPE_SETS as $encryption_type_set_i => $encryption_type_set) {
            $encryption_type_set_keys = array_keys($encryption_type_set);
            $encryption_type_set_key = $encryption_type_set_keys[0];
            $encryption_type_set_key_len = mb_strlen($encryption_type_set_key);
            $last_line_string =  mb_substr($string_to_lecical_analyze, -$encryption_type_set_key_len);
            $is_last_line_key =  strcmp($encryption_type_set_key, $last_line_string) === 0;
            if ($is_last_line_key) {
                $encryption_type_set[$encryption_type_set_key] = ENCRYPTION_TYPE_SETS[$encryption_type_set_i][$encryption_type_set_key];
                $string_to_lecical_analyze_removed_encryption_type_word = mb_substr($string_to_lecical_analyze, 0, mb_strlen($string_to_lecical_analyze) - $encryption_type_set_key_len - 1);//改行1回分も削除しておく
                break;
            }
            $i++;
            //下記は異常時、正常時はbreakで抜ける。
            $is_last = $i ===  $max_i;
            if ($is_last){
                var_dump($encryption_type_set);
                $keys = array_keys(ENCRYPTION_TYPE_SETS[0]);
                $key = $keys[0];
                $encryption_type_set[$key] = ENCRYPTION_TYPE_SETS[0][$key];
                var_dump($i);
                var_dump($max_i);
                var_dump($keys);
                var_dump($key);
                var_dump($encryption_type_set);
            }
        }


        parent::__construct($string_to_lecical_analyze_removed_encryption_type_word, $encryption_type_set);
    }
    function getDecryptedPlainText() {
        $plaintext = "";
        for($i = 0; $i < count($this->analyzed_tokens); $i++) {
            $content = $this->analyzed_tokens[$i][$this::KEY_TOKEN_CONTENT];
            $is_token_type_encryption = $this->analyzed_tokens[$i][$this::KEY_TOKEN_TYPE] === $this::TOKEN_TYPE_ENCRYPTION;
            if ($is_token_type_encryption) {
                $reserved_encryption_word_series = $this->reserved_encryption_word.$this->reserved_encryption_word;
                if( preg_match('{^'.$reserved_encryption_word_series.'}', $content) ){
                    $plaintext = $plaintext . preg_replace(
                            '{^'.$reserved_encryption_word_series.'}',
                            $this->reserved_encryption_word,
                            $content
                        );
                }
            } else {
                $plaintext = $plaintext . $content;
            }
        }
        return $plaintext;
    }
}


require_once "util.inc.php";
$is_server_request_post = $_SERVER["REQUEST_METHOD"] === "POST";
$input_text             = $_POST["plain_text"];
$output_text            = $_POST["encryption_text"];
$is_encryption          = $is_server_request_post && (array_key_exists("encryption", $_POST));
//$is_excahnge            = $is_server_request_post && (array_key_exists("exchange", $_POST));
$is_composite           = $is_server_request_post && (array_key_exists("composite", $_POST));
$encryption_type_set_i  = $_POST["encryption_type_set_i"];
$encryption_strength_i  = $_POST["encryption_strength_i"];

if ($is_encryption) {
    $input_text_word_count = mb_strlen($input_text);
    $encryption_type_set = ENCRYPTION_TYPE_SETS[$encryption_type_set_i];
    $reserved_encryption_word_appearance_rato = ENCRYPTION_STRENGTH[$encryption_strength_i];
    $tanuki_encryption = new TanukiEncryptionGenerator($input_text, $encryption_type_set, $reserved_encryption_word_appearance_rato);
    $temp_output_text = $tanuki_encryption->getTanukiEncryption();
    setcookie("input_text" ,$input_text, time() + 86400);
    setcookie("temp_output_text" ,$temp_output_text, time() + 86400);
    $output_text = $temp_output_text;
} elseif ($is_composite) {
    $decrypted_plaintext = new TanukiEncryptionDecoder($output_text);
    $temp_input_text = $decrypted_plaintext->getDecryptedPlainText();
    setcookie("temp_input_text" ,$input_text, time() + 86400);
    setcookie("temp_output_text" ,$temp_output_text, time() + 86400);
    $input_text = $temp_input_text;
// } elseif ($is_excahnge) {
//     $input_text = $_COOKIE["temp_output_text"];
//     $output_text = $_COKIE["input_text"];
}
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>たぬき暗号</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1 class="main_header">たぬき暗号</h1>
    </header>
    <main>
        <form action="" method="POST">
            <p class="select_header">暗号の種類を選択します。</p>
            <p class="selects">
                <select class="select" name="encryption_type_set_i">
                    <option value="0" <?= h(($encryption_type_set_i == 0)?"selected":""); ?>>ふつうのたぬき</option>
                    <option value="1" <?= h(($encryption_type_set_i == 1)?"selected":""); ?>>元気なたぬき</option>
                    <option value="2" <?= h(($encryption_type_set_i == 2)?"selected":""); ?>>つかれたぬき</option>
                </select>
            </p>
            <p class="select_header">暗号の強度を選択します。</p>
            <p class="selects">
                <select name="encryption_strength_i">
                    <option value="0" <?= h(($encryption_strength_i == 0)?"selected":""); ?>>ふつう</option>
                    <option value="1" <?= h(($encryption_strength_i == 1)?"selected":""); ?>>つよい</option>
                    <option value="2" <?= h(($encryption_strength_i == 2)?"selected":""); ?>>すごくつよい</option>
                </select>
            </p>
            <p class="textareas">もとの文</p>
            <p class="textareas"><textarea name="plain_text" cols="70" rows="10"><?= h($input_text); ?></textarea></p>
            <div class="buttons">
                <p><input type="submit" name="encryption" value="▽暗号化"></p>
<!--                 <p><input type="submit" name="exchange" value="入力出力交換"></p> -->
                <p><input type="submit" name="composite" value="復号化△"></p>
            </div>
            <p class="textareas">暗号文</p>
            <p class="textareas"><textarea name="encryption_text" cols="70" rows="10"><?= h($output_text); ?></textarea></p>
        </form>
    </main>
</body>
</html>