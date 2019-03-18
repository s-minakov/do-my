<?php
require_once 'models/Question.php';

$i=1;
$questions_count = array();
$count = Question:: getQuestionsCount (1, $i);

while ($count ['questions_count'] > 0){
    $questions_count[$i-1]['level'] = $i;
    $questions_count[$i-1]['questions_count'] = $count ['questions_count'];
    $i=$i+1;
    $count = Question:: getQuestionsCount (1, $i);
}

$data = 
[
'user_answer' => $_SESSION['user_answer'],
'questions_count' => $questions_count,
'level_access' => $_SESSION['level_access'],
'active_question' => $_SESSION['active_question']
];


echo json_encode($data);

die;