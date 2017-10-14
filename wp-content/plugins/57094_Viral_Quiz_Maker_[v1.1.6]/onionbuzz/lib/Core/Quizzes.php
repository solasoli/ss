<?php
namespace WpPluginAutoload\Core;

class Quizzes{

    private $item_id;
    private $wpdb;
    private $configs;
    public $settings_codes = array(
        'advanced' => array(
            0 => 'answer_status',
            1 => 'replay_button',
            2 => 'questions_order',
            3 => 'answers_order',
            4 => 'auto_scroll'
        )
    );

    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $oConfig = new Config();
        $this->configs = $oConfig->get();

        return 1;
    }

    public function getById($id)
    {
        $results = $this->wpdb->get_row( "SELECT * FROM `{$this->wpdb->prefix}ob_quizzes` WHERE id = {$id}", ARRAY_A );
        $results = stripslashes_deep($results);
        return $results;
    }

    public function getByPostId($id)
    {
        $results = $this->wpdb->get_row( "SELECT * FROM `{$this->wpdb->prefix}ob_quizzes` WHERE post_id = {$id}", ARRAY_A );
        $results = stripslashes_deep($results);
        return $results;
    }

    public function getResultsAll($query = '', $page = 1, $orderby = '', $ordertype = '', $quiz_id = ''){
        $results['items'] = $this->wpdb->get_results( "SELECT * FROM `{$this->wpdb->prefix}ob_results` WHERE 1 ", OBJECT );
        $results = stripslashes_deep($results);

        return json_encode($results);
    }
    public function getIdsList($limit = 5, $random = 0){
        $query = "";
        if($random == 1){
            $order = "ORDER BY RAND()";
        }
        $results = $this->wpdb->get_results( "SELECT id FROM `{$this->wpdb->prefix}ob_quizzes` WHERE 1 {$query} {$order} LIMIT {$limit}", ARRAY_A );
        $results = stripslashes_deep($results);
        return $results;
        #return json_encode($results);
    }
    public function getAll($query = '', $page = 1, $orderby = '', $ordertype = '', $feed = 'all'){
        if($orderby != ''){
            $order = "ORDER BY {$orderby}";
        }
        if($ordertype != ''){
            $order = $order . " {$ordertype}";
        }
        if($query == 'all'){
            $query = "";
        }
        else if($query != ''){
            $query = "AND {$this->wpdb->prefix}ob_quizzes.title COLLATE UTF8_GENERAL_CI LIKE '%{$query}%'";
        }

        // count items
        if($feed > 0){
            $results['items'] = $this->wpdb->get_results("SELECT {$this->wpdb->prefix}ob_quizzes.* FROM {$this->wpdb->prefix}ob_quizzes
            LEFT JOIN {$this->wpdb->prefix}ob_feed2quiz ON {$this->wpdb->prefix}ob_quizzes.id={$this->wpdb->prefix}ob_feed2quiz.quiz_id
            WHERE {$this->wpdb->prefix}ob_feed2quiz.feed_id = {$feed} {$query}", OBJECT);
            $items_count['total'] = $this->wpdb->num_rows;
        }
        else{
            $items_count = $this->wpdb->get_row( "SELECT count(0) as total FROM `{$this->wpdb->prefix}ob_quizzes` WHERE 1 {$query}", ARRAY_A );
        }

        // prepares for pagination
        $start = 0;
        $per_page = 10; // may be move to the global settings, but its only for admin panel
        $results['page'] = $page;
        $results['total_items'] = $items_count['total'];
        $results['total_pages'] = ceil($results['total_items'] / $per_page);

        if($results['page'] > 1){
            $start = ($results['page']-1)*($per_page);
        }

        // select items
        if($feed > 0){
            $results['items'] = $this->wpdb->get_results("SELECT {$this->wpdb->prefix}ob_quizzes.*, DATE_FORMAT({$this->wpdb->prefix}ob_quizzes.date_added,'%m/%d/%Y') as date_added, (SELECT count(0) FROM {$this->wpdb->prefix}ob_questions WHERE quiz_id = {$this->wpdb->prefix}ob_quizzes.id) as questions_count,(SELECT count(id) FROM {$this->wpdb->prefix}ob_results WHERE quiz_id = `{$this->wpdb->prefix}ob_quizzes`.`id`  AND flag_published = 1) as results_count FROM {$this->wpdb->prefix}ob_quizzes
            LEFT JOIN {$this->wpdb->prefix}ob_feed2quiz ON {$this->wpdb->prefix}ob_quizzes.id={$this->wpdb->prefix}ob_feed2quiz.quiz_id
            WHERE {$this->wpdb->prefix}ob_feed2quiz.feed_id = {$feed} {$query} {$order} LIMIT {$start}, {$per_page}", ARRAY_A); //AND wp_ob_quizzes.type = 1;
        }
        else{
            $results['items'] = $this->wpdb->get_results( "SELECT *, DATE_FORMAT({$this->wpdb->prefix}ob_quizzes.date_added,'%m/%d/%Y') as date_added, (SELECT count(id) FROM {$this->wpdb->prefix}ob_questions WHERE quiz_id = `{$this->wpdb->prefix}ob_quizzes`.`id` AND flag_publish = 1) as questions_count, (SELECT count(id) FROM {$this->wpdb->prefix}ob_results WHERE quiz_id = `{$this->wpdb->prefix}ob_quizzes`.`id`  AND flag_published = 1) as results_count FROM `{$this->wpdb->prefix}ob_quizzes` WHERE 1 {$query} {$order} LIMIT {$start}, {$per_page}", ARRAY_A );
        }

        foreach ($results['items'] as $k=>$v){
            $user_info = get_user_by('ID', $results['items'][$k]['user_id']);
            $results['items'][$k]['user_name'] = $user_info->user_login;
            $results['items'][$k]['players_count'] = 0;
            $results['items'][$k]['views_count'] = 0;
            $results['items'][$k]['preview_link'] = get_permalink($results['items'][$k]['post_id']);

            $select_feeds_count = $this->wpdb->get_row( "SELECT count(0) as total FROM `{$this->wpdb->prefix}ob_feed2quiz` WHERE 1 AND quiz_id = '{$results['items'][$k]['id']}'", ARRAY_A );
            $results['items'][$k]['feeds_count'] = $select_feeds_count['total'];
        }

        $results = stripslashes_deep($results);

        return json_encode($results);
    }
    public function getQuizzesIDS (){
        $results['items'] = $this->wpdb->get_results( "SELECT id, post_id FROM `{$this->wpdb->prefix}ob_quizzes`", ARRAY_A );
        $results = stripslashes_deep($results);
        return $results;
    }
    public function totalQuizzes (){
        return $this->wpdb->get_row( "SELECT count(0) as total FROM `{$this->wpdb->prefix}ob_quizzes` WHERE 1", ARRAY_A );
    }
    public function totalQuizResults ($quiz_id){
        $return = $this->wpdb->get_row( "SELECT count(0) as results_count FROM `{$this->wpdb->prefix}ob_results` WHERE quiz_id = '{$quiz_id}'", ARRAY_A );
        return $return['results_count'];
    }
    public function totalQuizQuestions ($quiz_id){
        $return = $this->wpdb->get_row( "SELECT count(0) as questions_count FROM `{$this->wpdb->prefix}ob_questions` WHERE quiz_id = '{$quiz_id}'", ARRAY_A );
        return $return['questions_count'];
    }
    public function getQuizFeeds($quiz_id){
        $results['items'] = $this->wpdb->get_results( "SELECT feed_id FROM `{$this->wpdb->prefix}ob_feed2quiz` WHERE quiz_id = $quiz_id", ARRAY_A );
        foreach ($results['items'] as $k=>$v){
            $results['feeds_array'][] = $results['items'][$k]['feed_id'];
        }
        return $results['feeds_array'];
    }
    public function cloneQuiz($item_id){

        $oSettings = new Settings();
        $oFeeds = new Feeds();
        $current_user = wp_get_current_user();

        // get quiz info from db & wp
        $data           = $this->getById($item_id);
        $data_settings  = $oSettings->getByQuizID($item_id);
        $data_feeds     = $this->getQuizFeeds($item_id);

        if($data['flag_published'] == 1){
            $data['wp_flag_published'] = "publish";
        }
        else{
            $data['wp_flag_published'] = "draft";
        }
        #$data['terms_ids'] = array_map( 'intval', $data['terms_ids'] );
        #$data['terms_ids'] = array_unique( $data['terms_ids'] );
        // save quiz info to db

        // save quiz post to wp
        $post = array(
            'ID' => '', //ID записи в случае её редактирования.
            'menu_order' => 0, //Если создаём страницу, то здесь устанавливаем порядок её отображения.
            'comment_status' => get_option('default_comment_status'), //[ 'closed' | 'open' ], 'closed' - комментирование закрыто.
            'ping_status' => get_option('default_ping_status'), //[ 'closed' | 'open' ] 'closed' - отключает pingbacks или trackbacks
            'post_author' => $current_user->ID, //ID автора поста.
            //'post_category' => $data['quiz_feeds'], //[ array(<category id>, <...>) ] //Добавление ID категорий.
            'post_content' => $data['description'], //[ <the text of the post> ] //Полный текст поста.
            'post_date' => current_time('mysql', 1), //[ Y-m-d H:i:s ] //Дата создания поста.
            //'post_date_gmt' => [ Y-m-d H:i:s ] //Дата создания поста по Гринвичу.
            'post_excerpt' => '', //[ <an excerpt> ] //Для ваших цитат из поста.
            'post_name' => '', //[ <the name> ] //Имя (slug) вашего поста.
            'post_parent' => '', //[ <post ID> ] //Установить родителя поста.
            'post_password' => '', //[ ? ] //Пароль для поста.
            'post_status' => $data['wp_flag_published'], //[ 'draft' | 'publish' | 'pending'| 'future' | 'private' ] //Статус для нового поста.
            'post_title' => $data['title'].' (copy)', //[ <the title> ] //Название вашего поста.
            'post_type' => $this->configs['onionbuzz_posttypes']['OB_POST_TYPE_NAME'], //[ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] //Тип поста: статья, страница, ссылка, пункт меню, иной тип.
            //'tags_input' => '', //[ '<tag>, <tag>, <...>' ] //Для тэгов.
            //'to_ping' => [ ? ] //?
            //'tax_input' => $custom_tax //Поддержка пользовательской таксономии.
        );
        $inserted_post['post_id'] = wp_insert_post( $post, $wp_error );

        //db add row
        $this->wpdb->insert(
            $this->wpdb->prefix . 'ob_quizzes',
            array(
                'post_id'           => intval($inserted_post['post_id']),
                'user_id'           => $current_user->ID,
                'type'              => $data['type'],
                'layout'            => $data['layout'],
                'title'             => $data['title']." (copy)",
                'description'       => $data['description'],
                //'featured_image'  => $data['featured_image'],
                //'image_caption'   => $data['image_caption'],
                'date_updated'      => current_time('mysql', 1),
                'date_added'        => current_time('mysql', 1),
                'flag_published'    => $data['flag_published']
            )
        );
        $new_item_id = $this->wpdb->insert_id;

        $this->save_settings(intval($new_item_id), $data_settings);

        $data['terms_ids'] = array();
        foreach ($data_feeds as $k=>$v){
            $tmp_term_id = $oFeeds->getFeedTermId($data_feeds[$k]);
            $data['terms_ids'][] = $tmp_term_id['term_id'];
            $this->wpdb->insert(
                $this->wpdb->prefix.'ob_feed2quiz',
                array(
                    'feed_id' => $data_feeds[$k],
                    'quiz_id' => $new_item_id
                )
            );
        }

        $data['terms_ids'] = array_map( 'intval', $data['terms_ids'] );
        $data['terms_ids'] = array_unique( $data['terms_ids'] );
        $term_taxonomy_ids = wp_set_object_terms( $inserted_post['post_id'], $data['terms_ids'], $this->configs['onionbuzz_posttypes']['OB_TAXONOMY_NAME'] );

        $return['id'] = $new_item_id;
        $return['success'] = 1;
        return json_encode($return);
    }
    public function getViewsByID (){
        return $this->wpdb->get_row( "SELECT views as total FROM `{$this->wpdb->prefix}ob_quizzes` WHERE 1", ARRAY_A );
    }
    public function save($item_id = 0, $data){

        // if attachment selected, get its wp id
        $attachment_id = intval($data['attachment_id']);

        $current_user = wp_get_current_user();
        #echo 'Username: ' . $current_user->user_login . '<br />';
        #echo 'User email: ' . $current_user->user_email . '<br />';
        #echo 'User first name: ' . $current_user->user_firstname . '<br />';
        #echo 'User last name: ' . $current_user->user_lastname . '<br />';
        #echo 'User display name: ' . $current_user->display_name . '<br />';
        #echo 'User ID: ' . $current_user->ID . '<br />';

        //draft or publish
        if($data['flag_published'] == 1){
            $data['wp_flag_published'] = "publish";
        }
        else{
            $data['wp_flag_published'] = "draft";
        }

        // the UPDATE
        if($item_id > 0){

            $return['success'] = 0;
            $return['action'] = 'UPDATE quiz id:'.$item_id;

            // get quiz wp post_id
            $item_info = $this->wpdb->get_row( "SELECT post_id FROM `{$this->wpdb->prefix}ob_quizzes` WHERE id = {$item_id} ORDER BY id ASC LIMIT 1", ARRAY_A );

            // wp update post
            $post = array(
                'ID' => $item_info['post_id'], //ID записи в случае её редактирования.
                'menu_order' => 0, //Если создаём страницу, то здесь устанавливаем порядок её отображения.
                'comment_status' => get_option('default_comment_status'), //[ 'closed' | 'open' ], 'closed' - комментирование закрыто.
                'ping_status' => get_option('default_ping_status'), //[ 'closed' | 'open' ] 'closed' - отключает pingbacks или trackbacks
                //'post_author' => intval($data['user_id']), //ID автора поста.
                //'post_category' => $data['quiz_feeds'], //[ array(<category id>, <...>) ] //Добавление ID категорий.
                'post_content' => $data['description'], //[ <the text of the post> ] //Полный текст поста.
                //'post_date' => current_time('mysql', 1), //[ Y-m-d H:i:s ] //Дата создания поста.
                //'post_date_gmt' => [ Y-m-d H:i:s ] //Дата создания поста по Гринвичу.
                'post_excerpt' => '', //[ <an excerpt> ] //Для ваших цитат из поста.
                'post_name' => '', //[ <the name> ] //Имя (slug) вашего поста.
                'post_parent' => '', //[ <post ID> ] //Установить родителя поста.
                'post_password' => '', //[ ? ] //Пароль для поста.
                'post_status' => $data['wp_flag_published'], //[ 'draft' | 'publish' | 'pending'| 'future' | 'private' ] //Статус для нового поста.
                'post_title' => $data['title'], //[ <the title> ] //Название вашего поста.
                'post_type' => $this->configs['onionbuzz_posttypes']['OB_POST_TYPE_NAME'], //[ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] //Тип поста: статья, страница, ссылка, пункт меню, иной тип.
                //'tags_input' => '', //[ '<tag>, <tag>, <...>' ] //Для тэгов.
                //'to_ping' => [ ? ] //?
                //'tax_input' => $custom_tax //Поддержка пользовательской таксономии.
            );
            wp_update_post( $post );

            //add post meta (featured image)
            if($attachment_id > 0){
                if ( ! add_post_meta( $item_info['post_id'], '_thumbnail_id', $attachment_id, true ) ) {
                    update_post_meta( $item_info['post_id'], '_thumbnail_id', $attachment_id );
                }
            }
            if($data['featured_image'] == ''){
                $attachment_id = get_post_meta( $item_info['post_id'], '_thumbnail_id', true );
                delete_post_meta($item_info['post_id'], '_thumbnail_id', $attachment_id);
            }

            // update quiz in db
            $this->wpdb->update(
                $this->wpdb->prefix.'ob_quizzes',
                array(
                    'title' => $data['title'],
                    'type' => $data['quiz_type'],
                    'layout' => $data['quiz_layout'],
                    'description' => $data['description'],
                    'featured_image' => $data['featured_image'],
                    'image_caption' => $data['image_caption'],
                    'date_updated' => current_time('mysql', 1),
                    'flag_published' => $data['flag_published'],
                    'flag_list_ranked' => $data['flag_list_ranked']
                    //'flag_main' => 0
                ),
                array(
                    'id' => intval($data['id'])
                )
            );

            $this->save_settings(intval($data['id']), $data);

            // link quiz to feeds
            $this->wpdb->delete( $this->wpdb->prefix.'ob_feed2quiz', array( 'quiz_id' => $item_id ), array( '%d' ) );

            // get main feed info
            $oFeeds = new Feeds();
            $data['main_feed'] = $oFeeds->getMain();
            // add main feed to feeds array
            $data['terms_ids'][] = $data['main_feed']['term_id'];

            $data['terms_ids'] = array_map( 'intval', $data['terms_ids'] );
            $data['terms_ids'] = array_unique( $data['terms_ids'] );
            #$term_taxonomy_ids = wp_set_object_terms( $item_info['post_id'], $data['terms_ids'], $this->configs['onionbuzz_posttypes']['OB_TAXONOMY_NAME'] );

            $this->wpdb->insert(
                $this->wpdb->prefix.'ob_feed2quiz',
                array(
                    'feed_id' => $data['main_feed']['id'],
                    'quiz_id' => $item_id
                )
            );

            if(count($data['quiz_feeds']) > 0){

                foreach($data['quiz_feeds'] as $k=>$v){
                    $this->wpdb->insert(
                        $this->wpdb->prefix.'ob_feed2quiz',
                        array(
                            'feed_id' => $data['quiz_feeds'][$k],
                            'quiz_id' => $item_id
                        )
                    );
                }
            }
            $term_taxonomy_ids = wp_set_object_terms( $item_info['post_id'], $data['terms_ids'], $this->configs['onionbuzz_posttypes']['OB_TAXONOMY_NAME'] );

            $return['success'] = 1;
            $return['id'] = $item_id;
        }
        // the INSERT
        else
        {
            $return['success'] = 0;
            $return['action'] = 'INSERT quiz. Assign to feeds:'.implode(',',$data['quiz_feeds']);

            //setup selected terms (feeds)
            $data['terms_ids'] = array_map( 'intval', $data['terms_ids'] );
            $data['terms_ids'] = array_unique( $data['terms_ids'] );

            // wp insert post
            $post = array(
                'ID' => '', //ID записи в случае её редактирования.
                'menu_order' => 0, //Если создаём страницу, то здесь устанавливаем порядок её отображения.
                'comment_status' => get_option('default_comment_status'), //[ 'closed' | 'open' ], 'closed' - комментирование закрыто.
                'ping_status' => get_option('default_ping_status'), //[ 'closed' | 'open' ] 'closed' - отключает pingbacks или trackbacks
                'post_author' => $current_user->ID, //ID автора поста.
                //'post_category' => $data['quiz_feeds'], //[ array(<category id>, <...>) ] //Добавление ID категорий.
                'post_content' => $data['description'], //[ <the text of the post> ] //Полный текст поста.
                'post_date' => current_time('mysql', 1), //[ Y-m-d H:i:s ] //Дата создания поста.
                //'post_date_gmt' => [ Y-m-d H:i:s ] //Дата создания поста по Гринвичу.
                'post_excerpt' => '', //[ <an excerpt> ] //Для ваших цитат из поста.
                'post_name' => '', //[ <the name> ] //Имя (slug) вашего поста.
                'post_parent' => '', //[ <post ID> ] //Установить родителя поста.
                'post_password' => '', //[ ? ] //Пароль для поста.
                'post_status' => $data['wp_flag_published'], //[ 'draft' | 'publish' | 'pending'| 'future' | 'private' ] //Статус для нового поста.
                'post_title' => $data['title'], //[ <the title> ] //Название вашего поста.
                'post_type' => $this->configs['onionbuzz_posttypes']['OB_POST_TYPE_NAME'], //[ 'post' | 'page' | 'link' | 'nav_menu_item' | custom post type ] //Тип поста: статья, страница, ссылка, пункт меню, иной тип.
                //'tags_input' => '', //[ '<tag>, <tag>, <...>' ] //Для тэгов.
                //'to_ping' => [ ? ] //?
                //'tax_input' => $custom_tax //Поддержка пользовательской таксономии.
            );
            $inserted_post['post_id'] = wp_insert_post( $post, $wp_error );

            if($inserted_post['post_id'] > 0) {

                //add post meta (featured image)
                if($attachment_id > 0){
                    if ( ! add_post_meta( $inserted_post['post_id'], '_thumbnail_id', $attachment_id, true ) ) {
                        update_post_meta( $inserted_post['post_id'], '_thumbnail_id', $attachment_id );
                    }
                    if ( ! add_post_meta( $inserted_post['post_id'], '_bimber_single_options', $attachment_id, true ) ) {
                        update_post_meta( $inserted_post['post_id'], '_thumbnail_id', $attachment_id );
                    }
                }

                //db add row
                $this->wpdb->insert(
                    $this->wpdb->prefix . 'ob_quizzes',
                    array(
                        'post_id' => $inserted_post['post_id'],
                        'user_id' => $current_user->ID,
                        'type' => $data['quiz_type'],
                        'layout' => $data['quiz_layout'],
                        'title' => $data['title'],
                        'description' => $data['description'],
                        'featured_image' => $data['featured_image'],
                        'image_caption' => $data['image_caption'],
                        'date_updated' => current_time('mysql', 1),
                        'date_added' => current_time('mysql', 1),
                        'flag_published' => $data['flag_published'],
                        'flag_list_ranked' => $data['flag_list_ranked']
                    )
                );
                $item_id = $this->wpdb->insert_id;

                $this->save_settings(intval($item_id), $data);

                // add quiz feeds to db and to wp
                if (count($data['quiz_feeds']) > 0) {
                    $term_taxonomy_ids = wp_set_object_terms( $inserted_post['post_id'], $data['terms_ids'], $this->configs['onionbuzz_posttypes']['OB_TAXONOMY_NAME'] );
                    foreach ($data['quiz_feeds'] as $k => $v) {
                        $this->wpdb->insert(
                            $this->wpdb->prefix . 'ob_feed2quiz',
                            array(
                                'feed_id' => $data['quiz_feeds'][$k],
                                'quiz_id' => $item_id
                            )
                        );
                    }
                }
                else
                {
                    // add to default feed if feeds not selected
                    $oFeeds = new Feeds();
                    $data['main_feed'] = $oFeeds->getMain();
                    $data['terms_ids'][] = $data['main_feed']['term_id'];
                    $data['terms_ids'] = array_map( 'intval', $data['terms_ids'] );
                    $data['terms_ids'] = array_unique( $data['terms_ids'] );
                    $term_taxonomy_ids = wp_set_object_terms( $inserted_post['post_id'], $data['terms_ids'], $this->configs['onionbuzz_posttypes']['OB_TAXONOMY_NAME'] );
                    $this->wpdb->insert(
                        $this->wpdb->prefix . 'ob_feed2quiz',
                        array(
                            'feed_id' => $data['main_feed']['id'],
                            'quiz_id' => $item_id
                        )
                    );
                }
                if ($item_id > 0) {
                    $return['success'] = 1;
                    $return['id'] = $item_id;
                }
            }
        }

        return json_encode($return);

    }
    public function save_settings($item_id = 0, $data){
        $this->wpdb->delete( $this->wpdb->prefix.'ob_settings', array( 'quiz_id' => $item_id ), array( '%d' ) );
        #print_r($data);
        foreach($this->settings_codes['advanced'] as $k=>$v){

            $this->wpdb->insert(
                $this->wpdb->prefix.'ob_settings',
                array(
                    'quiz_id' => $item_id,
                    'type'  => 'advanced',
                    'value' => $data[$this->settings_codes['advanced'][$k]],
                    'code' => $this->settings_codes['advanced'][$k]
                )

            );
        }

        $return['success'] = 1;
        $return['id'] = $item_id;
        return json_encode($return);
    }

    public function delete($item_id){
        $item_id = intval($item_id);
        if($item_id > 0){
            // delete quiz
            $item_info = $this->wpdb->get_row( "SELECT post_id FROM `{$this->wpdb->prefix}ob_quizzes` WHERE id = {$item_id} LIMIT 1", ARRAY_A );
            $this->wpdb->delete( $this->wpdb->prefix.'ob_quizzes', array( 'id' => $item_id ), array( '%d' ) );
            $this->wpdb->delete( $this->wpdb->prefix.'ob_feed2quiz', array( 'quiz_id' => $item_id ), array( '%d' ) );
            $this->wpdb->delete( $this->wpdb->prefix.'ob_questions', array( 'quiz_id' => $item_id ), array( '%d' ) );
            $this->wpdb->delete( $this->wpdb->prefix.'ob_answers', array( 'quiz_id' => $item_id ), array( '%d' ) );
            $this->wpdb->delete( $this->wpdb->prefix.'ob_results', array( 'quiz_id' => $item_id ), array( '%d' ) );
            $this->wpdb->delete( $this->wpdb->prefix.'ob_settings', array( 'quiz_id' => $item_id ), array( '%d' ) );
            // delete quiz results and questions, answers, feed2quiz
            if($item_info['post_id'] > 0){
                // delete wp_post and media
                wp_delete_post( $item_info['post_id'], 1 );
            }

            $return['action'] = 'DELETE';
            $return['success'] = 1;
        }
        else{
            $return['success'] = 0;
        }
        $return['action'] = 'DELETE';
        $return['success'] = 1;
        return json_encode($return);

    }
    public function getResult($data){

        $item_id = intval($data['id']);
        $quiz_type = $data['quiz_type'];

        $oResults = new Results();
        if($quiz_type == 1){
            $result = $oResults->getResultByPointsTrivia($item_id, $data['points']);
        }
        else if($quiz_type == 5){
            $result = $oResults->getResultByPointsTrivia($item_id, $data['points']);
        }
        else if($quiz_type == 2){
            $result = $oResults->getResultPersonality($item_id, $data['selectedAnswers']);
        }

        $return['quiz_id'] = $item_id;
        $return['points'] = $data['points'];

        $return['title']            = $result['title'];
        $return['description']      = $result['description'];
        $return['featured_image']   = '<img src="'.$result['featured_image'].'">';
        $return['image_caption']    = $result['image_caption'];

        if(!isset($result['featured_image']) || $result['featured_image'] == ''){
            $return['is_image'] = 0;
        }
        else{
            $return['is_image'] = 1;
        }

        $return['success'] = 1;
        return ($return);

    }
}