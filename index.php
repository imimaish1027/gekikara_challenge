<?php

ini_set('log_errors', 'off');
ini_set('error_log', 'php_log');
session_start();

$foods = array();

class Gender
{
    const MAN = 1;
}

abstract class Thing
{
    protected $name;
    protected $hp;
    public function setName($str)
    {
        $this->name = $str;
    }
    public function getName()
    {
        return $this->name;
    }
    public function setHp($num)
    {
        $this->hp = $num;
    }
    public function getHp()
    {
        return $this->hp;
    }
}

class Human extends Thing
{
    protected $gender;
    protected $fire;
    protected $attackMin;
    protected $attackMax;

    public function __construct($name, $gender, $hp, $attackMin, $attackMax, $fire)
    {
        $this->name = $name;
        $this->gender = $gender;
        $this->hp = $hp;
        $this->attackMin = $attackMin;
        $this->attackMax = $attackMax;
        $this->fire = $fire;
    }

    public function setGender($num)
    {
        $this->gender = $num;
    }
    public function getGender()
    {
        return $this->gender;
    }

    public function setFire($num)
    {
        $this->fire = $num;
    }
    public function getFire()
    {
        return $this->fire;
    }
    public function attack()
    {
        $eatingPoint = mt_rand($this->attackMin, $this->attackMax);
        if (5 <= $_SESSION['burning'] && $_SESSION['burning'] < 10) {
            $eatingPoint *= 1.2;
        } elseif (10 <= $_SESSION['burning'] && $_SESSION['burning'] < 15) {
            $eatingPoint *= 1.4;
        } elseif (15 <= $_SESSION['burning'] && $_SESSION['burning'] < 20) {
            $eatingPoint *= 1.6;
        } elseif (20 <= $_SESSION['burning'] && $_SESSION['burning'] < 25) {
            $eatingPoint *= 1.8;
        } elseif ($_SESSION['burning'] >= 25) {
            $eatingPoint *= 2.0;
        }
        $eatingPoint = floor($eatingPoint);
        $_SESSION['food']->setHp($_SESSION['food']->getHp() - $eatingPoint);
        History::set($eatingPoint . 'ポイント食べた');
    }
}

class Food extends Thing
{
    private $img;
    protected $attackMin;
    protected $attackMax;
    private $hot = '';
    private $point = '';

    public function __construct($name, $hp, $img, $attackMin, $attackMax, $hot, $point)
    {
        $this->name = $name;
        $this->hp = $hp;
        $this->img = $img;
        $this->attackMin = $attackMin;
        $this->attackMax = $attackMax;
        $this->hot = $hot;
        $this->point = $point;
    }
    public function attack()
    {
        $attackPoint = mt_rand($this->attackMin, $this->attackMax);
        if ($_SESSION['food']->getHot() > 0) {
            History::set($attackPoint . 'ポイントのダメージを受けた');
            $_SESSION['human']->setHp($_SESSION['human']->getHp() - $attackPoint);
        } elseif ($_SESSION['food']->getHot() == 0) {
            History::set($attackPoint . 'ポイント回復した！');
            $_SESSION['human']->setHp($_SESSION['human']->getHp() + $attackPoint);
        }
    }

    public function setHot($num)
    {
        $this->hot = filter_var($num, FILTER_VALIDATE_INT);
    }
    public function setPoint($num)
    {
        $this->point = filter_var($num, FILTER_VALIDATE_INT);
    }
    public function getImg()
    {
        return $this->img;
    }
    public function getHot()
    {
        return $this->hot;
    }
    public function getPoint()
    {
        return $this->point;
    }
}

interface HistoryInterface
{
    public static function set($str);
    public static function clear();
}
class History implements HistoryInterface
{
    public static function set($str)
    {
        if (empty($_SESSION['history'])) $_SESSION['history'] = '';
        $_SESSION['history'] .= $str . '<br>';
    }
    public static function clear()
    {
        unset($_SESSION['history']);
    }
}

$human = new Human('カラオ', Gender::MAN, 500, 80, 100, 0);
$foods[] = new Food('フランクフルト', 100, 'img/food1.jpg', 20, 40, 1, 100);
$foods[] = new Food('レッドチキン', 100, 'img/food2.jpg', 20, 40, 1, 100);
$foods[] = new Food('麻婆豆腐', 300, 'img/food3.jpg', 30, 50, 3, 350);
$foods[] = new Food('エビチリ', 200, 'img/food4.jpg', 25, 45, 2, 220);
$foods[] = new Food('キムチ鍋', 300, 'img/food5.jpg', 30, 50, 3, 350);
$foods[] = new Food('スパイシーカレー', 400, 'img/food6.jpg', 35, 55, 4, 480);
$foods[] = new Food('激辛ラーメン', 400, 'img/food7.jpg', 35, 55, 4, 480);
$foods[] = new Food('担々麺', 200, 'img/food8.jpg', 25, 45, 2, 220);
$foods[] = new Food('ハバネロ', 10, 'img/food9.jpg', 200, 200, 5, 600);
$foods[] = new Food('わさびアイスクリーム', 50, 'img/food10.jpg', 20, 40, 1.5, 50);
$foods[] = new Food('バニラアイス', 50, 'img/food11.jpg', 50, 50, 0, 50);

function createHuman()
{
    global $human;
    $_SESSION['human'] = $human;
}
function createFood()
{
    global $foods;
    $food = $foods[mt_rand(0, 10)];
    History::set('店員「' . $food->getName() . 'になります。」');
    $_SESSION['food'] = $food;
}
function init()
{
    History::clear();
    History::set('激辛チャレンジスタート!');
    $_SESSION['ateFoodCount'] = 0;
    $_SESSION['score'] = 0;
    $_SESSION['burning'] = 0;
    createHuman();
    createFood();
}
function gameOver()
{
    $_SESSION = array();
}

if (!empty($_POST)) {
    $startFlg = (!empty($_POST['start'])) ? true : false;
    $ruleFlg = (!empty($_POST['rule'])) ? true : false;
    $eatFlg = (!empty($_POST['eat'])) ? true : false;
    $drinkFlg = (!empty($_POST['drink'])) ? true : false;
    $changeFlg = (!empty($_POST['change'])) ? true : false;
    $topFlg = (!empty($_POST['top'])) ? true : false;
    $clearFlg = false;
    error_log('POSTされた！');

    if ($startFlg) {
        init();
    } elseif ($ruleFlg) {
    } elseif ($topFlg) {
        gameOver();
    } elseif ($eatFlg) {

        if ($_SESSION['food']->getHot() > 0) {
            History::set('カラオ「辛いっ！」');
        } elseif ($_SESSION['food']->getHot() == 0) {
            History::set('カラオ「冷たいっ！」');
        }

        $_SESSION['human']->attack();
        $_SESSION['food']->attack();

        if ($_SESSION['human']->getHp() > 500) {
            $_SESSION['human']->setHp(500);
        }

        if ($_SESSION['food']->getHp() <= 0) {
            History::set($_SESSION['food']->getName() . 'を完食した！');
            $_SESSION['score'] += $_SESSION['food']->getPoint();
            $_SESSION['ateFoodCount'] += 1;
            if ($_SESSION['ateFoodCount'] == 20 || $_SESSION['human']->getHp() <= 0) {
                $clearFlg = true;
            }
            $_SESSION['burning'] += $_SESSION['food']->getHot();
            createFood();
        }
    } elseif ($drinkFlg) {
        History::set('カラオは50ポイント回復した');
        $_SESSION['human']->setHp($_SESSION['human']->getHp() + 50);
        if ($_SESSION['human']->getHp() > 500) {
            $_SESSION['human']->setHp(500);
        }
        $_SESSION['ateFoodCount'] += 1;
        if ($_SESSION['ateFoodCount'] == 20) {
            $clearFlg = true;
        }
    } elseif ($changeFlg) {
        History::set('カラオ「料理を変えてください。」');
        createFood();
    }
    $_POST = array();
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>GEKIKARA CHALLENGE</title>
    <link href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" rel="stylesheet">
    <link rel="shortcut icon" href="./img/toga.png" type="image/x-icon">
    <link href="style.css" rel="stylesheet" type="text/css">
</head>

<body>
    <h1>　GEKIKARA <i class="fa fa-fire-alt"></i> CHALLENGE</h1>
    <?php if (empty($_SESSION) && $ruleFlg === true) { ?>
        <div class="rule">
            <div class="rule__info">
                <p class="rule__info__text">
                    激辛料理をたくさん食べてスコアを稼ごう！<br>
                    ・激辛料理を食べれば食べるほどHOTがアップ<br>
                    ・HOTが1上がる毎に食べる量が上昇！<br>
                    ・水やデザートも食べた品数にカウントされる<br>
                    ・高得点の超激辛料理が出てくることも…<br><br>

                    クリア条件<br>
                    「自分のHPが0」または「食べた品数が20品に達する」<br>

                </p>
            </div>
            <form method="post" class="rule__nation">
                <button type="submit" name="start" value="start" class="buttonA">ゲームスタート</button>
            </form>
        </div>

    <?php } elseif (empty($_SESSION) || $topFlg === true) { ?>
        <div class="top">
            <div class="top__img">
                <img src="./img/top.jpg">
            </div>
            <form method="post" class="top__menu">
                <button type="submit" name="start" value="start" class="buttonA">ゲームスタート</button><br>
                <button type="submit" name="rule" value="rule" class="buttonA">ルール</button>
            </form>

        </div>
    <?php } elseif (!empty($_SESSION) && $clearFlg === true) { ?>
        <div class="result">
            <div class="result__area">
                <p>ゲームクリア！</p>
                <div class="result__area--ate">
                    食べた品数　
                    <?php echo $_SESSION['ateFoodCount']; ?> 品
                </div>
                <div class="result__area--score">
                    スコア　
                    <?php echo $_SESSION['score']; ?> 辛
                </div>
            </div>
            <form method="post" class="result__menu">
                <button type="submit" name="top" value="top" class="buttonA"><i class="fas fa-chevron-up"></i>トップ</button>
                <button type="submit" name="start" value="start" class="buttonA"><i class="fas fa-undo"></i>リトライ</button>
            </form>
        </div>

    <?php } else { ?>
        <div class="game">
            <div class="game__up">
                <div class="game__up__left">
                    <div class="left__ate left__info">
                        食べた品数<br>
                        <span class="ate--count"><?php echo $_SESSION['ateFoodCount']; ?> 品<span>
                    </div>
                    <div class="left__score left__info">
                        スコア<br>
                        <span class="score--count"><?php echo $_SESSION['score']; ?> 辛</span>
                    </div>

                    <form method="post" class="left__menu">
                        <button type="submit" name="top" value="top" class="buttonA"><i class="fas fa-chevron-up"></i>トップ</button>
                        <button type="submit" name="start" value="start" class="buttonA"><i class="fas fa-undo"></i>リスタート</button>
                    </form>


                </div>

                <div class="game__food">
                    <h2 class="food--title"><?php echo $_SESSION['food']->getName() ?></h2>
                    <div>
                        <img src="<?php echo $_SESSION['food']->getImg() ?>" style="height: 200px; width:210px;" class="food-img">
                    </div>
                    <div class="game__food__hp">HP　<meter min="0" max="<?php switch ($_SESSION['food']->getHot()) {
                                                                            case 1:
                                                                                echo 100;
                                                                                break;
                                                                            case 2:
                                                                                echo 200;
                                                                                break;
                                                                            case 3:
                                                                                echo 300;
                                                                                break;
                                                                            case 4:
                                                                                echo 400;
                                                                                break;
                                                                            case 5:
                                                                                echo 10;
                                                                                break;
                                                                            default:
                                                                                echo 50;
                                                                                break;
                                                                        }
                                                                        ?>" value="<?php echo $_SESSION['food']->getHp() ?>" class="gage food__hp-gage"></meter>
                        <div style="padding-left: 10px;"> <?php echo $_SESSION['food']->getHp() ?>
                            / <?php switch ($_SESSION['food']->getHot()) {
                                    case 1:
                                        echo 100;
                                        break;
                                    case 2:
                                        echo 200;
                                        break;
                                    case 3:
                                        echo 300;
                                        break;
                                    case 4:
                                        echo 400;
                                        break;
                                    case 5:
                                        echo 10;
                                        break;
                                    default:
                                        echo 50;
                                        break;
                                }
                                ?></div>
                    </div>
                    <div class="game__food__hot"><span class="food--karasa">辛さ</span>
                        <img src=<?php switch ($_SESSION['food']->getHot()) {
                                        case 0:
                                            echo "img/kara_level0.jpg";
                                            break;
                                        case 1:
                                            echo "img/kara_level1.jpg";
                                            break;
                                        case 2:
                                            echo "img/kara_level2.jpg";
                                            break;
                                        case 3:
                                            echo "img/kara_level3.jpg";
                                            break;
                                        case 4:
                                            echo "img/kara_level4.jpg";
                                            break;
                                        case 5:
                                            echo "img/kara_level5.jpg";
                                            break;
                                        default:
                                            echo "img/kara_level1.jpg";
                                            break;
                                    }
                                    ?> style="width: 150px; height: 50px;"></div>
                </div>
                <div class="game__history">
                    <div id="scroll-inner">
                        <?php echo (!empty($_SESSION['history'])) ? $_SESSION['history'] : ''; ?>
                    </div>
                </div>
            </div>

            <div class="status">
                <div class="status__info">
                    <div class="status__img">
                        <img src="img/karataro01.jpg"><br>
                        カラオ
                    </div>
                    <div class="status__hp">
                        <p class="status__hp__hot"> <span class="status--hp">HP</span>　<meter min="0" max="500" value="<?php echo $_SESSION['human']->getHp() ?>" class="gage hp-gage"></meter> <?php echo $_SESSION['human']->getHp(); ?> / 500</p>
                        <div class="status__jonetu__hot"><span class="status--jonetu">HOT</span>
                            <img src=<?php $burn = $_SESSION['burning'];
                                        switch ($burn) {
                                            case 0:
                                                echo "img/hirihiri_level00.jpg";
                                                break;
                                            case $burn < 5:
                                                echo "img/hirihiri_level00.jpg";
                                                break;
                                            case 5 <= $burn && $burn < 10:
                                                echo "img/hirihiri_level01.jpg";
                                                break;
                                            case 10 <= $burn && $burn < 15:
                                                echo "img/hirihiri_level02.jpg";
                                                break;
                                            case 15 <= $burn && $burn < 20:
                                                echo "img/hirihiri_level03.jpg";
                                                break;
                                            case 20 <= $burn && $burn < 25:
                                                echo "img/hirihiri_level04.jpg";
                                                break;
                                            case 25 <= $burn:
                                                echo "img/hirihiri_level05.jpg";
                                                break;
                                        }
                                        ?> style="width: 200px; height: 60px;">
                        </div>
                        <p class="status--taberu">食べる量 × <?php $burn = $_SESSION['burning'];
                                                            switch ($burn) {
                                                                case 0:
                                                                    echo 1.00;
                                                                    break;
                                                                case $burn < 5:
                                                                    echo 1.00;
                                                                    break;
                                                                case 5 <= $burn && $burn < 10:
                                                                    echo 1.2;
                                                                    break;
                                                                case 10 <= $burn && $burn < 15:
                                                                    echo 1.4;
                                                                    break;
                                                                case 15 <= $burn && $burn < 20:
                                                                    echo 1.6;
                                                                    break;
                                                                case 20 <= $burn && $burn < 25:
                                                                    echo 1.8;
                                                                    break;
                                                                case 25 <= $burn:
                                                                    echo 2.0;
                                                                    break;
                                                            }
                                                            ?></p>
                    </div>
                    <form method="post" class="status__command">
                        <button type="submit" name="eat" value="食べる" class="buttonB command--eat"><img src="./img/button01.jpg" style="width: 100px; height:100px;"><br>食べる</button>
                        <button type="submit" name="drink" value="水を飲む" class="buttonB command--drink"><img src="./img/button02.jpg" style="width: 100px; height:100px;"><br>水を飲む</button>
                        <button type="submit" name="change" value="交換" class="buttonB command--change"><img src="./img/button003.jpg" style="width: 100px; height:100px;"><br>交換</button>

                    </form>
                </div>
            </div>
        </div>

    <?php } ?>
</body>

<script>
    let target = document.getElementById('scroll-inner');
    target.scrollIntoView(false);
</script>

</html>