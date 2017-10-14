<?php
namespace WpPluginAutoload\Core;

class Questions{

    private $item_id;
    private $wpdb;

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;

        return 1;
    }

    public function getById($id)
    {
        $results = $this->wpdb->get_row( "SELECT * FROM `{$this->wpdb->prefix}ob_questions` WHERE id = {$id}", ARRAY_A );
        $results = stripslashes_deep($results);
        return $results;
    }

    public function getAllByQuizID($quiz_id = '', $order = ""){
        if($order == ""){
            $order = "`position` ASC, `id` ASC";
        }
        if($quiz_id > 0) {
            $ip = $_SERVER['HTTP_CLIENT_IP']?$_SERVER['HTTP_CLIENT_IP']:($_SERVER['HTTP_X_FORWARDE‌​D_FOR']?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
            $results['items'] = $this->wpdb->get_results("SELECT *, (SELECT COUNT(0) FROM `{$this->wpdb->prefix}ob_vote2question` WHERE question_id = `{$this->wpdb->prefix}ob_questions`.`id` ) as votes, (SELECT COUNT(0) FROM `{$this->wpdb->prefix}ob_vote2question` WHERE question_id = `{$this->wpdb->prefix}ob_questions`.`id` AND ip = '{$ip}' ) as voted FROM `{$this->wpdb->prefix}ob_questions` WHERE quiz_id = '{$quiz_id}' AND flag_publish = 1 ORDER BY {$order}", ARRAY_A);
            $results = stripslashes_deep($results);
        }

        #return json_encode($results);
        return $results;
    }
    public function prevFromId($id, $args = array()){
        if(isset($args['quiz_id']) && intval($args['quiz_id'] > 0))
        {
            $and_query = " AND quiz_id = '{$args['quiz_id']}'";
        }
        return $results = $this->wpdb->get_row( "SELECT id FROM `{$this->wpdb->prefix}ob_questions` WHERE id < {$id} {$and_query} ORDER BY id DESC LIMIT 1", ARRAY_A );
    }
    public function nextFromId($id, $args = array()){
        if(isset($args['quiz_id']) && intval($args['quiz_id'] > 0))
        {
            $and_query = " AND quiz_id = '{$args['quiz_id']}'";
        }
        return $results = $this->wpdb->get_row( "SELECT id FROM `{$this->wpdb->prefix}ob_questions` WHERE id > {$id} {$and_query} ORDER BY id ASC LIMIT 1", ARRAY_A );
    }
    public function getAll($query = '', $page = 1, $orderby = '', $ordertype = '', $quiz_id = ''){
        if($quiz_id > 0){
            $query_setup .= "AND quiz_id = '{$quiz_id}'";
        }
        $results['items'] = $this->wpdb->get_results( "SELECT *,(SELECT count(0) FROM `{$this->wpdb->prefix}ob_answers` WHERE question_id = `{$this->wpdb->prefix}ob_questions`.`id`) as answers_count,(SELECT count(0) FROM `{$this->wpdb->prefix}ob_answers` WHERE question_id = `{$this->wpdb->prefix}ob_questions`.`id` AND flag_correct = 1) as correct_count, (SELECT `type` FROM `{$this->wpdb->prefix}ob_quizzes` WHERE `{$this->wpdb->prefix}ob_quizzes`.`id` = quiz_id) as quiz_type FROM `{$this->wpdb->prefix}ob_questions` WHERE 1 $query_setup ORDER BY `position` ASC", ARRAY_A );
        $results = stripslashes_deep($results);

        return json_encode($results);
    }

    public function getQuizId($id)
    {
        $results = $this->wpdb->get_row( "SELECT quiz_id FROM `{$this->wpdb->prefix}ob_questions` WHERE id = {$id}", ARRAY_A );
        $results = stripslashes_deep($results);
        return $results;
    }
    public function saveQuestionVote($id)
    {
        $ip = $_SERVER['HTTP_CLIENT_IP']?$_SERVER['HTTP_CLIENT_IP']:($_SERVER['HTTP_X_FORWARDE‌​D_FOR']?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR']);
        $check = $this->wpdb->get_row( "SELECT count(0) as voted FROM `{$this->wpdb->prefix}ob_vote2question` WHERE question_id = '{$id}' AND ip = '{$ip}'", ARRAY_A );

        if($check['voted'] == 0) {
            $return['success'] = 0;
            $current_votes = $this->getQuestionVotesCount($id);
            $after_votes = $current_votes['votes'] + 1;

            $this->wpdb->insert(
                $this->wpdb->prefix . 'ob_vote2question',
                array(
                    'question_id' => intval($id),
                    'ip' => $ip,
                    'date_added' => current_time('mysql', 1)
                )
            );
            $item_id = $this->wpdb->insert_id;

            if ($item_id > 0) {

                $this->wpdb->update(
                    $this->wpdb->prefix . 'ob_questions',
                    array(
                        'upvotes' => $after_votes
                    ),
                    array(
                        'id' => intval($id)
                    )
                );

                $return['id'] = $item_id;
                $return['votes'] = $after_votes;
                $return['success'] = 1;
            }

        }
        else{
            $return['success'] = 0;
            $current_votes = $this->getQuestionVotesCount($id);
            $after_votes = $current_votes['votes'] - 1;

            $this->wpdb->delete( $this->wpdb->prefix.'ob_vote2question', array( 'question_id' => $id, 'ip' => $ip ), array( '%d', '%s' ) );

            $this->wpdb->update(
                $this->wpdb->prefix . 'ob_questions',
                array(
                    'upvotes' => $after_votes
                ),
                array(
                    'id' => intval($id)
                )
            );

            $return['votes'] = $after_votes;
            $return['success'] = 1;
        }

        return ($return);
    }
    public function getQuestionVotesCount($id)
    {
        $results = $this->wpdb->get_row( "SELECT COUNT(0) as votes FROM `{$this->wpdb->prefix}ob_vote2question` WHERE question_id = {$id}", ARRAY_A );
        #$results = stripslashes_deep($results);
        return $results;
    }
    public function totalQuestionAnswers ($question_id){
        $return = $this->wpdb->get_row( "SELECT count(0) as answers_count FROM `{$this->wpdb->prefix}ob_answers` WHERE question_id = '{$question_id}'", ARRAY_A );
        return $return['answers_count'];
    }
    public function reorderQuestions ($data){
        foreach ($data['values'] as $k=>$v){
            $this->wpdb->update(
                $this->wpdb->prefix.'ob_questions',
                array(
                    'position' => $data['values'][$k]['position']
                ),
                array(
                    'id' => intval($data['values'][$k]['question'])
                )
            );
        }
        #print_r($data['values']);
    }
    public function save($item_id = 0, $data){

        // if attachment selected, get its wp id
        $attachment_id = intval($data['attachment_id']);

        // the UPDATE
        if($item_id > 0){

            $return['success'] = 0;
            $return['action'] = 'UPDATE question id:'.$item_id;

            // update question in db
            $this->wpdb->update(
                $this->wpdb->prefix.'ob_questions',
                array(
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'featured_image' => $data['featured_image'],
                    'image_caption' => $data['image_caption'],
                    'secondary_image' => $data['secondary_image'],
                    'secondary_image_caption' => $data['secondary_image_caption'],
                    'answers_type' => $data['answers_type'],
                    'mediagrid_type' => $data['mediagrid_type'],
                    'date_updated' => current_time('mysql', 1),
                    'explanation_title' => $data['explanation_title'],
                    'explanation' => $data['explanation'],
                    'explanation_image' => $data['explanation_image'],
                    'flag_explanation' => $data['flag_explanation'],
                    'flag_pagebreak' => $data['flag_pagebreak'],
                    'flag_casesensitive' => $data['flag_casesensitive'],
                    'flag_publish' => $data['flag_published']
                ),
                array(
                    'id' => intval($item_id)
                )
            );

            if($data['answers_type'] == "match"){
                $oAnswers = new Answers();
                $oAnswers->saveFromString($item_id, $data['question_match_answers']);
            }

            $return['success'] = 1;
            $return['id'] = $item_id;
            $return['quiz_id'] = $data['quiz_id'];
        }
        // the INSERT
        else
        {
            $return['success'] = 0;
            $return['action'] = 'INSERT question. Assign to quiz:'.$data['quiz_id'];

            $max_position = $this->wpdb->get_row( "SELECT MAX(`position`) as last_position FROM `{$this->wpdb->prefix}ob_questions` WHERE quiz_id = '{$data['quiz_id']}'", ARRAY_A);
            $set_position = $max_position['last_position'] + 1;
            #print_r($data);
            //db add row
            $this->wpdb->insert(
                $this->wpdb->prefix . 'ob_questions',
                array(
                    'quiz_id' => $data['quiz_id'],
                    'title' => $data['title'],
                    'position' => $set_position,
                    'description' => $data['description'],
                    'featured_image' => $data['featured_image'],
                    'image_caption' => $data['image_caption'],
                    'secondary_image' => $data['secondary_image'],
                    'secondary_image_caption' => $data['secondary_image_caption'],
                    'answers_type' => $data['answers_type'],
                    'mediagrid_type' => $data['mediagrid_type'],
                    'date_added' => current_time('mysql', 1),
                    'date_updated' => current_time('mysql', 1),
                    'explanation_title' => $data['explanation_title'],
                    'explanation' => $data['explanation'],
                    'explanation_image' => $data['explanation_image'],
                    'flag_explanation' => $data['flag_explanation'],
                    'flag_pagebreak' => $data['flag_pagebreak'],
                    'flag_casesensitive' => $data['flag_casesensitive'],
                    'flag_publish' => $data['flag_published']
                )
            );
            $item_id = $this->wpdb->insert_id;

            /*if($this->wpdb->last_error !== '') :
                $query = htmlspecialchars( $this->wpdb->last_query );
                print "<div id='error'>
                <p class='wpdberror'><strong>WordPress database error:</strong> [$str]<br />
                <code>$query</code></p>
                </div>";
            endif;*/

            if($data['answers_type'] == "match"){
                $oAnswers = new Answers();
                $oAnswers->saveFromString($item_id, $data['question_match_answers']);
            }

            if ($item_id > 0) {
                $return['success'] = 1;
                $return['id'] = $item_id;
                $return['quiz_id'] = $data['quiz_id'];
            }
        }

        return json_encode($return);

    }

    public function delete($item_id){
        $item_id = intval($item_id);
        if($item_id > 0){

            $this->wpdb->delete( $this->wpdb->prefix.'ob_answers', array( 'question_id' => $item_id ), array( '%d' ) );
            $this->wpdb->delete( $this->wpdb->prefix.'ob_questions', array( 'id' => $item_id ), array( '%d' ) );

            $return['action'] = 'DELETE';
            $return['success'] = 1;
        }
        else{
            $return['success'] = 0;
        }

        return json_encode($return);

    }
}