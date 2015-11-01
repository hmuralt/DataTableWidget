<table id="<?php echo $tableId; ?>" class="table table-striped table-bordered" cellspacing="0" width="100%">
    <?php
    if (!empty($records["columns"])) {
        $headingsHtml = '';

        foreach ($records["columns"] as $id => $heading) {
            $headingsHtml .= '<th>' . $heading . '</th>';
        }

        echo "<thead><tr>$headingsHtml</tr></thead>";
    }
    if (!empty($records["data"])) {
        $rowsHtml = '';

        foreach ($records["data"] as $row) {
            $rowsHtml .= '<tr>';
            foreach ($row as $cell) {
                $rowsHtml .= "<td>$cell</td>";
            }
            $rowsHtml .= '</tr>';
        }

        echo "<tbody>$rowsHtml</tbody>";
    }
    ?>
</table>