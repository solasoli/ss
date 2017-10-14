<?php
namespace WpPluginAutoload\Core;

class Results{

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
        $results = $this->wpdb->get_row( "SELECT * FROM `{$this->wpdb->prefix}ob_results` WHERE id = {$id}", ARRAY_A );
        $results = stripslashes_deep($results);
        return $results;
    }
    public function getByQuizId($quiz_id)
    {
        $results = $this->wpdb->get_results( "SELECT * FROM `{$this->wpdb->prefix}ob_results` WHERE quiz_id = {$quiz_id} ", ARRAY_A );
        $results = stripslashes_deep($results);
        return $results;
    }
    public function prevFromId($id, $args = array()){
        if(isset($args['quiz_id']) && intval($args['quiz_id'] > 0))
        {
            $and_query = " AND quiz_id = '{$args['quiz_id']}'";
        }
        return $results = $this->wpdb->get_row( "SELECT id FROM `{$this->wpdb->prefix}ob_results` WHERE id < {$id} {$and_query} ORDER BY id DESC LIMIT 1", ARRAY_A );
    }
    public function nextFromId($id, $args = array()){
        if(isset($args['quiz_id']) && intval($args['quiz_id'] > 0))
        {
            $and_query = " AND quiz_id = '{$args['quiz_id']}'";
        }
        return $results = $this->wpdb->get_row( "SELECT id FROM `{$this->wpdb->prefix}ob_results` WHERE id > {$id} {$and_query} ORDER BY id ASC LIMIT 1", ARRAY_A );
    }
    public function getAll($query = '', $page = 1, $orderby = '', $ordertype = '', $quiz_id = ''){
        $query_setup = '';
        if($quiz_id > 0){
            $query_setup .= "AND quiz_id = '{$quiz_id}'";
        }
        if($orderby != ''){
            $order = "ORDER BY {$orderby}";
        }
        if($ordertype != ''){
            $order = "".$order . " {$ordertype}";
        }
        $results['items'] = $this->wpdb->get_results( "SELECT * FROM `{$this->wpdb->prefix}ob_results` WHERE 1 $query_setup {$order}", ARRAY_A );
        foreach($results['items'] as $k=>$v){
            $results['items'][$k]['quiz_type'] = $this->wpdb->get_row( "SELECT `type` FROM `{$this->wpdb->prefix}ob_quizzes` WHERE id = {$results['items'][$k]['quiz_id']}", ARRAY_A );
            $results['items'][$k]['quiz_type'] = $results['items'][$k]['quiz_type']['type'];
            $results['items'][$k]['condition_less'] = $this->wpdb->get_row( "SELECT CAST(conditions as UNSIGNED) as conditions FROM `{$this->wpdb->prefix}ob_results` WHERE quiz_id = {$results['items'][$k]['quiz_id']} AND conditions < {$results['items'][$k]['conditions']} AND flag_published = 1 ORDER BY conditions DESC LIMIT 1", ARRAY_A );
            $results['items'][$k]['condition_less'] = $results['items'][$k]['condition_less']['conditions']+1;
        }
        $results = stripslashes_deep($results);

        return json_encode($results);
    }
    public function getResultPersonality($quiz_id, $selected_answers)
    {

        $oAnswers = new Answers();
        $quiz_results = $this->getByQuizId($quiz_id);
        $answers_ids_sql = join("','",$selected_answers);

        foreach ($quiz_results as $k=>$v){
            $results[$quiz_results[$k]['id']]['id']             = $quiz_results[$k]['id'];
            $results[$quiz_results[$k]['id']]['title']          = $quiz_results[$k]['title'];
            $results[$quiz_results[$k]['id']]['description']    = $quiz_results[$k]['description'];
            $results[$quiz_results[$k]['id']]['featured_image'] = $quiz_results[$k]['featured_image'];
            $results[$quiz_results[$k]['id']]['image_caption']  = $quiz_results[$k]['image_caption'];
            $results[$quiz_results[$k]['id']]['points']         = 0;
            $results[$quiz_results[$k]['id']]['q'] = $this->wpdb->get_row( "SELECT sum(points) as points FROM `{$this->wpdb->prefix}ob_answer2result` WHERE result_id = {$results[$quiz_results[$k]['id']]['id']} AND answer_id IN ('$answers_ids_sql')", ARRAY_A);
            $top[$results[$quiz_results[$k]['id']]['id']]['points'] = $results[$quiz_results[$k]['id']]['q']['points'];
        }
        $winners = array_keys($top, max($top));

        $return = stripslashes_deep($results[$winners[0]]);
        return $return;
    }
    public function getResultByPointsTrivia($quiz_id, $points)
    {
        #$points = $points+1;
        $results = $this->wpdb->get_row( "SELECT *,CAST(conditions as UNSIGNED) as conditions FROM `{$this->wpdb->prefix}ob_results` WHERE quiz_id = {$quiz_id} AND conditions >= $points AND flag_published = 1 order by conditions ASC LIMIT 1", ARRAY_A );
        $results['description'] = $results['description'];
        $results = stripslashes_deep($results);
        return $results;
    }
    public function getResultsByQuizID($quiz_id)
    {
        $results = $this->wpdb->get_results( "SELECT * FROM `{$this->wpdb->prefix}ob_results` WHERE quiz_id = {$quiz_id} AND flag_published = 1", ARRAY_A );
        $results = stripslashes_deep($results);
        return $results;
    }
    public function getResultPointsByResultIDAnswerID($result_id, $answer_id)
    {
        $results = $this->wpdb->get_row( "SELECT points FROM `{$this->wpdb->prefix}ob_answer2result` WHERE result_id = {$result_id} AND answer_id = {$answer_id}", ARRAY_A );
        $results = stripslashes_deep($results['points']);
        return $results;
    }
    public function getQuizId($id)
    {
        $results = $this->wpdb->get_row( "SELECT quiz_id FROM `{$this->wpdb->prefix}ob_results` WHERE id = {$id}", ARRAY_A );
        $results = stripslashes_deep($results);
        return $results;
    }

    public function save($item_id = 0, $data){

        // if attachment selected, get its wp id
        $attachment_id = intval($data['attachment_id']);

        // the UPDATE
        if($item_id > 0){

            $return['success'] = 0;
            $return['action'] = 'UPDATE result id:'.$item_id;

            // update result in db
            $this->wpdb->update(
                $this->wpdb->prefix.'ob_results',
                array(
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'conditions' => $data['conditions'],
                    'featured_image' => $data['featured_image'],
                    'image_caption' => $data['image_caption'],
                    'flag_published' => $data['flag_published']
                ),
                array(
                    'id' => intval($item_id)
                )
            );

            $return['success'] = 1;
            $return['id'] = $item_id;
            $return['quiz_id'] = $data['quiz_id'];
        }
        // the INSERT
        else
        {
            $return['success'] = 0;
            $return['action'] = 'INSERT result. Assign to quiz:'.$data['quiz_id'];

            //db add row
            $this->wpdb->insert(
                $this->wpdb->prefix . 'ob_results',
                array(
                    'quiz_id' => $data['quiz_id'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'conditions' => $data['conditions'],
                    'featured_image' => $data['featured_image'],
                    'image_caption' => $data['image_caption'],
                    'flag_published' => $data['flag_published']
                )
            );
            $item_id = $this->wpdb->insert_id;

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
            // delete quiz

            $this->wpdb->delete( $this->wpdb->prefix.'ob_answer2result', array( 'result_id' => $item_id ), array( '%d' ) );
            $this->wpdb->delete( $this->wpdb->prefix.'ob_results', array( 'id' => $item_id ), array( '%d' ) );

            $return['action'] = 'DELETE';
            $return['success'] = 1;
        }
        else{
            $return['success'] = 0;
        }

        return json_encode($return);

    }
}