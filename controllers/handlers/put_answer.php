<?php
require_once 'models/Answer.php';
require_once 'models/Question.php';
require_once 'models/User.php';

$postData = file_get_contents('php://input');

$data = json_decode($postData, true);

$id_question = $data['id_question'];
$answer = new Answer(1, $id_question);
$id_answer = $data['id_answer'];
$sign_bot = $data['sign_bot'];
foreach ($answer->id_answer as $key => $id_answer_check){ //определяем позицию ответа в массиве и делаем замену id на позицию
    if ($id_answer == $id_answer_check) {$id_answer = $key;}
}

$answer_is_true = $answer->is_true[$id_answer];
$answer_is_true_comment = $answer->is_true_comment[$id_answer];
$time_answer = time();

$retry = 0;
$i = count($_SESSION['user_answer']);
foreach ($_SESSION['user_answer'] as $key => $user_answer) { //проверяем на наличие ранее отвеченного вопроса и если ответ есть - перезаписываем
    if ($user_answer['id_question'] == $id_question) {
        $i = $key;
        $retry = 1;
    }
    else {
    $i = count($_SESSION['user_answer']);
    }
}

(($i == "") ? $i = 0 : $i = $i);
$_SESSION['user_answer'][$i]['id_question'] = $id_question;
$_SESSION['user_answer'][$i]['id_answer'] = $id_answer;
$_SESSION['user_answer'][$i]['answer_is_true'] = $answer_is_true;
$_SESSION['user_answer'][$i]['answer_is_true_comment'] = $answer_is_true_comment;
$_SESSION['user_answer'][$i]['time_answer'] = $time_answer;

$data = array();

// проверка на бота по времени ответов на вопросы и в случае провала проверки  записываем признак бота в сессию
if ($i == 0) {$delta = $_SESSION['user_answer'][$i]['time_answer'] - $_SESSION['time_start'];}
else {$delta = $_SESSION['user_answer'][$i]['time_answer'] - $_SESSION['user_answer'][$i-1]['time_answer'];}

if ($retry == 0) {
    if ($delta <= 10) {$_SESSION['bot'] = 1;}
}
else {
    if ($delta <= 3) {$_SESSION['bot'] = 1;}
}
if ($sign_bot == 1) {$_SESSION['bot'] = 1;}


// определяем доступ к уровням и текущую позицию по уровню, если вопрос последний на 1 уровне и правильных ответов больше 90% - идет запись ответов в базу под сессией пользователя.
$last_session_id = $_COOKIE ['PHPSESSID'];
$ip_user = $_SESSION['ip_user'];

$user = new User ($last_session_id);

$count_1 = Question:: getQuestionsCount (1, 1);
$count_2 = Question:: getQuestionsCount (1, 2);
$count_3 = Question:: getQuestionsCount (1, 3);
$count_question = count($_SESSION['user_answer']);
$count_true = 0;
if ($count_question == $count_1['questions_count']){
    for ($i = 0; $i <= $count_1['questions_count']-1; $i++) {
        if ($_SESSION['user_answer'][$i]['answer_is_true'] == 1) {$count_true++;}
    }
    if ($count_true >= 0.9 * $count_1['questions_count']) {$_SESSION['level_access'] = 2;}
}

if ($count_question == $count_2['questions_count']){
    for ($i = $count_1; $i <= $count_2['questions_count']-1; $i++) {
        if ($_SESSION['user_answer'][$i]['answer_is_true'] == 1) {$count_true++;}
    }
    if ($count_true >= 0.9 * $count_2['questions_count']) {$_SESSION['level_access'] = 3;}
}
if ($count_question == $count_3['questions_count']){
    for ($i = $count_2; $i <= $count_3['questions_count']-1; $i++) {
        if ($_SESSION['user_answer'][$i]['answer_is_true'] == 1) {$count_true++;}
    }
    if ($count_true >= 0.9 * $count_3['questions_count']) {$_SESSION['level_access'] = 4;}
}

$level_access = $_SESSION['level_access'];

if ($level_access >= 2){
    if ($count_question == $count_1['questions_count']){
        if ($user->id_user == NULL) {User::signUpAuto($last_session_id, $ip_user); $user = new User ($last_session_id);}
        foreach ($_SESSION['user_answer'] as $key => $user_answer) {
            User::putUserAnswer($user->id_user, $user_answer['id_question'], $user_answer['id_answer']);
        }
    } else {
        User::putUserAnswer($user->id_user, $_SESSION['user_answer'][$count_question-1]['id_question'], $_SESSION['user_answer'][$count_question-1]['id_answer']);
    }
}


//  отправляем ответ на фронт об истинности вопроса и комментарии к нему
$data =
[
'answer_is_true' => $answer_is_true ,
'answer_is_true_comment' => $answer_is_true_comment
];
 
echo json_encode($data);

die;