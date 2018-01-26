<?php
    $this->load->view("dashboard/properties/_framework_header");
?>

	<section class="content-header">
        <h1>
            Add a Property<small></small>
        </h1>
        <a class="btn btn-primary btn-sm pull-right ng-binding" href="http://localhost:8080/syrian/dashboard/props/list"><?= get_lang("Return to the list") ?></a>
    </section> <!-- section .content-header -->
    
    <div class="content">
        <?php $this->load->view("dashboard/properties/_create_user_check"); ?>
        <?php $this->load->view("dashboard/properties/create_content"); ?>
    </div> <!-- div .content -->

<?php
    $this->load->view("dashboard/properties/_framework_end");
?>

