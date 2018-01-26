<?php
    $this->load->view("dashboard/properties/_framework_header");
?>
	<section class="content-header">
        <h1>
            Property List <small></small>
        </h1>
        <a class="btn btn-primary btn-sm pull-right ng-binding" href="http://localhost:8080/syrian/dashboard/props/create"><?= get_lang("Add New Property") ?></a>
    </section> <!-- section .content-header -->
    
    <div class="content">
        <?php $this->load->view("dashboard/properties/list_content"); ?>
    </div> <!-- div .content -->

<?php
    $this->load->view("dashboard/properties/_framework_end");
?>

