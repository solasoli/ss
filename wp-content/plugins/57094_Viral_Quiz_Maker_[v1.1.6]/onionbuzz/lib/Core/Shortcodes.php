<?php
namespace WpPluginAutoload\Core;

class Shortcodes{

    private $wpdb;
    private $loader;
    private $templating;

    public function __construct($loader, $templating)
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->templating = $templating;
        $this->loader = $loader;

        add_shortcode('onionbuzz', array($this, 'onionbuzz_func'));

    }

    public function onionbuzz_func( $atts, $content = null)	{
        if ( is_single() || is_page() ) {
            $atts = shortcode_atts(array(
                'message' => '',
                'post-id' => 0,
                'quiz_id' => 0,
                'quiz-id' => 0,
                'title' => 1,
                'description' => 1,
                'image' => 1,
                'embed' => 1
            ), $atts, 'onionbuzz');

            if($atts['quiz_id'] > 0){
                $atts['quiz-id'] = $atts['quiz_id'];
            }
            if ($atts['quiz-id'] > 0 || $atts['post-id'] > 0) {

                $data['options'] = $atts;
                $oQuizzes = new Quizzes();
                $oQuestions = new Questions();
                $oAnswers = new Answers();
                $oResults = new Results();

                // get settings
                $oSettings = new Settings();
                $data['settings']['general']        = $oSettings->getByType('general');
                $data['settings']['appearance']     = $oSettings->getByType('appearance');
                $data['settings']['social']         = $oSettings->getByType('social');
                $data['settings']['optin']          = $oSettings->getByType('optin');

                #echo '<pre>';
                #print_r($data['settings']);
                #echo '</pre>';
                //

                if ($atts['quiz-id'] > 0) {

                    $data['quiz_info'] = $oQuizzes->getById($atts['quiz-id']);
                    $data['settings']['quiz']           = $oSettings->getByQuizID($data['quiz_info']['id']);

                } else if ($atts['post-id'] > 0) {

                    $data['quiz_info'] = $oQuizzes->getByPostId($atts['post-id']);
                    $data['settings']['quiz']           = $oSettings->getByQuizID($data['quiz_info']['id']);

                }

                if($data['settings']['quiz']['questions_order'] == "random"){
                    $data['quiz_questions'] = $oQuestions->getAllByQuizID($data['quiz_info']['id'], "RAND()");
                }
                else if($data['settings']['quiz']['questions_order'] == "upvotes"){
                    $data['quiz_questions'] = $oQuestions->getAllByQuizID($data['quiz_info']['id'], "`upvotes` DESC");
                }
                else {
                    $data['quiz_questions'] = $oQuestions->getAllByQuizID($data['quiz_info']['id']);
                }

                if(count($data['quiz_questions']['items']) > 0){
                    foreach ($data['quiz_questions']['items'] as $k => $v) {
                        if($data['settings']['quiz']['answers_order'] == "random"){
                            $data['quiz_questions']['items'][$k]['answers'] = $oAnswers->getAllByQuestionID($data['quiz_questions']['items'][$k]['id'], "RAND()");
                        }
                        else{
                            $data['quiz_questions']['items'][$k]['answers'] = $oAnswers->getAllByQuestionID($data['quiz_questions']['items'][$k]['id']);
                        }

                    }
                }
                #print_r($data['quiz_questions']['items'][$k]['answers']['items']);

                // check if user see result lock or not (db row)
                $ip = $_SERVER['HTTP_CLIENT_IP']?$_SERVER['HTTP_CLIENT_IP']:($_SERVER['HTTP_X_FORWARDE‌​D_FOR']?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
                $quiz_id = $data['quiz_info']['id'];
                $current_user = wp_get_current_user(); //
                $user_id = intval($current_user->ID);
                $check_lock_status = $this->wpdb->get_row( "SELECT count(0) as total FROM `{$this->wpdb->prefix}ob_result_unlocks` WHERE 1 AND quiz_id = '{$quiz_id}' AND user_id = '{$user_id}' AND ip = '{$ip}'", ARRAY_A );
                if($check_lock_status['total'] == 1){
                    $data['settings']['optin']['settings_resultlock'] = 0;
                }

                $dontlock_stories_array = explode(',', $data['settings']['optin']['lock_ignore_quizids']);
                if(in_array($quiz_id,$dontlock_stories_array)){
                    $data['settings']['optin']['settings_resultlock'] = 0;
                }

                $data['content'] = $content;
                if($data['quiz_info']['layout'] == 'fulllist') {
                    ob_start();
                    $this->templating->render('frontend/templates/quiz-shortcode', $data);
                    $result = ob_get_contents();
                    ob_end_clean();
                }
                else if($data['quiz_info']['layout'] == 'slider') {
                    ob_start();
                    $this->templating->render('frontend/templates/quiz-shortcode-slider', $data);
                    $result = ob_get_contents();
                    ob_end_clean();
                }

                return $result;
            } else {
                return "Wrong parameters";
            }
        }

    }



}