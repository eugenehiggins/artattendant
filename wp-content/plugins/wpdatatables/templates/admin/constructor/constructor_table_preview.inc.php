<?php defined('ABSPATH') or die("Cannot access pages directly."); ?>

<table class="table table-striped table-condensed">

    <thead>
    <tr>
        <?php foreach (array_keys($result[0]) as $header) { ?>
            <th><?php echo $header ?></th>
        <?php } ?>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($result as $row) { ?>
        <tr>
            <?php foreach ($row as $cell) { ?>
                <td><?php echo $cell ?></td>
            <?php } ?>
        </tr>
    <?php } ?>
    </tbody>

</table>