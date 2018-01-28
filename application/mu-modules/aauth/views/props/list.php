<?php
    $this->load->view("dashboard/properties/_framework_header");
?>
	<section class="content-header">
        <h1>
            <?= get_lang("Property List") ?> <small></small>
        </h1>
        <a class="btn btn-primary btn-sm pull-right ng-binding" href="<?= lang_site_url("dashboard/props/create") ?>"><?= get_lang("Add New Property") ?></a>
    </section> <!-- section .content-header -->
    
    <div class="content">
        <?php $this->load->view("dashboard/properties/list_content"); ?>
    </div> <!-- div .content -->

<?php
    $this->load->view("dashboard/properties/_framework_end");
?>

