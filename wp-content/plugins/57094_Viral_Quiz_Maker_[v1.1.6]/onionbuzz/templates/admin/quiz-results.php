<div class="laqm-admin">

    <?php
    $data['templating']->render('admin/menu-top', $data);
    ?>

    <div class="laqm-content">

        <div class="laqm-item-navigation">
            <div class="laqm-item-back"><a class="laqm-btn laqm-btn-blue no-left-margin" href="?page=la_onionbuzz_dashboard">&larr; Back</a></div>
            <div class="laqm-item-name"><span><?=$data['edit_quiz']['title']?></span></div>
            <!--<div class="laqm-item-nextprev pull-right">
                <a class="laqm-btn laqm-btn-blue with-icon" href="#feed/:id/close"><span class="icon-arrow-left"></span></a>
                <a class="laqm-btn laqm-btn-blue with-icon" href="#feed/:id/close"><span class="icon-arrow-right"></span></a>
            </div>-->
        </div>

        <div style="clear: both;"></div>

            <!--<div class="laqm-alerts">

                <div class="alert alert-warning" role="alert">
                    Some answers have not been linked to results. <a href="#" class="alert-link">LINK</a>
                </div>

            </div> -->

        <div class="laqm-tabs-tools-container">

            <div class="laqm-tabs">
                <?php
                $data['templating']->render('admin/quiz-tabs', $data);
                ?>
            </div>
            <div class="laqm-tools">
                <div class="laqm-tools-item pull-right">
                    <a class="laqm-btn laqm-btn-blue with-icon" href="<?=$data['edit_quiz']['preview_link']?>" target="_blank"><span class="icon-ico-preview"></span></a>
                    <a class="laqm-btn laqm-btn-blue" href="#quiz/<?=$data['edit_quiz']['id']?>/shortcode">Shortcode</a>
                </div>
            </div>

            <div style="clear: both;"></div>

        </div>

        <div class="laqm-tab-content">

            <div id="laqm-results-list" class="laqm-items-list"></div>

        </div>

    </div>
</div>
<?php
$data['templating']->render('admin/templates/quiz', $data);
?>