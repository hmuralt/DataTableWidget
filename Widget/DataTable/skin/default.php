<div class="ipsContainer">
    <?php if (ipIsManagementState() && isset($error)) {
        echo $error;
    } else {
        echo isset($dataTableHtml) ? $dataTableHtml : '';
    } ?>
</div>


