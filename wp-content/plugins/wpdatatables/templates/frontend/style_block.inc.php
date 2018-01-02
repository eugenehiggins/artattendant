<?php defined('ABSPATH') or die("Cannot access pages directly."); ?>

<style>
<?php if(!empty($wdtFontColorSettings['wdtTableFontColor'])){ ?>
/* table font color */
.wpDataTablesWrapper table.wpDataTable {
	color: <?php echo $wdtFontColorSettings['wdtTableFontColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtHeaderBaseColor'])){ ?>
/* th background color */
.wpDataTablesWrapper table.wpDataTable thead th,
.wpDataTablesWrapper table.wpDataTable thead th.sorting {
	background-color: <?php echo $wdtFontColorSettings['wdtHeaderBaseColor'] ?> !important;
    background-image: none !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtHeaderBorderColor'])){ ?>
/* th border color */
.wpDataTablesWrapper table.wpDataTable thead th,
.wpDataTablesWrapper table.wpDataTable thead th.sorting {
	border-color: <?php echo $wdtFontColorSettings['wdtHeaderBorderColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtHeaderFontColor'])){ ?>
/* th font color */
.wpDataTablesWrapper table.wpDataTable thead th {
	color: <?php echo $wdtFontColorSettings['wdtHeaderFontColor'] ?> !important;
}
.wpDataTablesWrapper table.wpDataTable thead th.sorting:after,
.wpDataTablesWrapper table.wpDataTable thead th.sorting_asc:after {
	border-bottom-color: <?php echo $wdtFontColorSettings['wdtHeaderFontColor'] ?> !important;
}
.wpDataTablesWrapper table.wpDataTable thead th.sorting_desc:after {
	border-top-color: <?php echo $wdtFontColorSettings['wdtHeaderFontColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtHeaderActiveColor'])){ ?>
/* th active/hover background color */
.wpDataTablesWrapper table.wpDataTable thead th.sorting_asc,
.wpDataTablesWrapper table.wpDataTable thead th.sorting_desc,
.wpDataTablesWrapper table.wpDataTable thead th.sorting:hover {
	background-color: <?php echo $wdtFontColorSettings['wdtHeaderActiveColor'] ?> !important;
    background-image: none !important;
}
<?php } ?>

<?php if(!empty($wdtFontColorSettings['wdtTableInnerBorderColor'])){ ?>
/* td inner border color */
.wpDataTablesWrapper table.wpDataTable td {
	border-color: <?php echo $wdtFontColorSettings['wdtTableInnerBorderColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtTableOuterBorderColor'])){ ?>
/* table outer border color */
.wpDataTablesWrapper table.wpDataTable tr:last-child td {
	border-bottom-color: <?php echo $wdtFontColorSettings['wdtTableOuterBorderColor'] ?> !important;
}
.wpDataTablesWrapper table.wpDataTable tr td:first-child {
	border-left-color: <?php echo $wdtFontColorSettings['wdtTableOuterBorderColor'] ?> !important;
}
.wpDataTablesWrapper table.wpDataTable tr td:last-child {
	border-right-color: <?php echo $wdtFontColorSettings['wdtTableOuterBorderColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtOddRowColor'])){ ?>
/* odd rows background color */
.wpDataTablesWrapper table.wpDataTable tr.odd td {
	background-color: <?php echo $wdtFontColorSettings['wdtOddRowColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtEvenRowColor'])){ ?>
/* even rows background color */
.wpDataTablesWrapper table.wpDataTable tr.even td,
.wpDataTablesWrapper table.has-columns-hidden tr.row-detail > td {
	background-color: <?php echo $wdtFontColorSettings['wdtEvenRowColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtActiveOddCellColor'])){ ?>
/* odd rows active background color */
.wpDataTablesWrapper table.wpDataTable tr.odd td.sorting_1 {
	background-color: <?php echo $wdtFontColorSettings['wdtActiveOddCellColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtActiveEvenCellColor'])){ ?>
/* even rows active background color */
.wpDataTablesWrapper table.wpDataTable tr.even td.sorting_1 {
	background-color: <?php echo $wdtFontColorSettings['wdtActiveEvenCellColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtHoverRowColor'])){ ?>
/* rows hover background color */
.wpDataTablesWrapper table.wpDataTable tr.odd:hover > td,
.wpDataTablesWrapper table.wpDataTable tr.odd:hover > td.sorting_1,
.wpDataTablesWrapper table.wpDataTable tr.even:hover > td,
.wpDataTablesWrapper table.wpDataTable tr.even:hover > td.sorting_1 {
	background-color: <?php echo $wdtFontColorSettings['wdtHoverRowColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtSelectedRowColor'])){ ?>
/* selected rows background color */
.wpDataTablesWrapper table.wpDataTable tr.odd.selected > td,
.wpDataTablesWrapper table.wpDataTable tr.odd.selected > td.sorting_1,
.wpDataTablesWrapper table.wpDataTable tr.even.selected > td,
.wpDataTablesWrapper table.wpDataTable tr.even.selected > td.sorting_1 {
	background-color: <?php echo $wdtFontColorSettings['wdtSelectedRowColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonColor'])){ ?>
/* buttons background color */
.wpDataTables .checkbox-filter.btn,
.wdt-frontend-modal .btn,
div.dt-button-collection a.dt-button.active:not(.disabled) {
	background-color: <?php echo $wdtFontColorSettings['wdtButtonColor'] ?> !important;
    background-image: none !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonBorderColor'])){ ?>
/* buttons border color */
.wpDataTables .checkbox-filter.btn,
.wdt-frontend-modal .btn:not(.dropdown-toggle),
div.dt-button-collection a.dt-button.active:not(.disabled) {
    <?php if ($wdtFontColorSettings['wdtButtonBorderColor']) { ?>
    border: 1px solid;
	border-color: <?php echo $wdtFontColorSettings['wdtButtonBorderColor'] ?> !important;
    <?php } ?>
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonFontColor'])){ ?>
/* buttons font color */
.wpDataTables .checkbox-filter.btn,
.wpDataTables .selecter .selecter-selected,
.wdt-frontend-modal .btn:not(.dropdown-toggle),
div.dt-button-collection a.dt-button.active:not(.disabled) {
	color: <?php echo $wdtFontColorSettings['wdtButtonFontColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtBorderRadius'])){ ?>
<?php $wdtBorderRadius = (int)$wdtFontColorSettings['wdtBorderRadius']; ?>
/* buttons and inputs border radius */
.wpDataTables .checkbox-filter.btn,
.wdt-frontend-modal .btn:not(.dropdown-toggle),
div.dt-button-collection a.dt-button.active:not(.disabled) {
	border-radius: <?php echo $wdtBorderRadius ?>px !important;
}
.wpDataTables input {
    border-radius: <?php echo $wdtBorderRadius ?>px !important;
}
<?php echo $wdtSelecterRadius = $wdtBorderRadius-1 > 0 ? $wdtBorderRadius-1 : 0; ?>
.wpDataTables .selecter .selecter-item:last-child {
	border-radius: 0px 0px <?php echo $wdtSelecterRadius ?>px <?php echo $wdtSelecterRadius ?>px !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonBackgroundHoverColor'])){ ?>
/** buttons background hover color */
.wpDataTables .checkbox-filter.btn:hover,
.wdt-frontend-modal .btn:not(.dropdown-toggle).btn:hover,
div.dt-button-collection a.dt-button.active:not(.disabled):hover {
	background-color: <?php echo $wdtFontColorSettings['wdtButtonBackgroundHoverColor'] ?> !important;
    background-image: none !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonBorderHoverColor'])){ ?>
/** buttons hover border color */
.wpDataTables .checkbox-filter.btn:hover,
.wdt-frontend-modal .btn:not(.dropdown-toggle).btn:hover,
div.dt-button-collection a.dt-button.active:not(.disabled):hover {
	border-color: <?php echo $wdtFontColorSettings['wdtButtonBorderHoverColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonFontHoverColor'])){ ?>
/** buttons hover font color */
.wpDataTables .checkbox-filter.btn:hover,
.wdt-frontend-modal .btn:not(.dropdown-toggle).btn:hover,
div.dt-button-collection a.dt-button.active:not(.disabled):hover {
	color: <?php echo $wdtFontColorSettings['wdtButtonFontHoverColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtButtonFontHoverColor'])){ ?>
/** buttons hover font color */
.wpDataTables .checkbox-filter.btn:hover,
.wdt-frontend-modal .btn:not(.dropdown-toggle).btn:hover,
div.dt-button-collection a.dt-button.active:not(.disabled):hover {
	color: <?php echo $wdtFontColorSettings['wdtButtonFontHoverColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtModalFontColor'])){ ?>
/** modals font color */
.wpDataTables .picker .picker-handle,
.wpDataTables .picker.focus .picker-handle {
	border-color: <?php echo $wdtFontColorSettings['wdtModalFontColor'] ?> !important;
}
.wpDataTables .picker.picker-checkbox .picker-flag,
.wpDataTables .picker .picker-label,
.wdt-frontend-modal .modal-dialog .modal-content,
.wpDataTables .picker__box,
.wpDataTables .picker__weekday {
	color: <?php echo $wdtFontColorSettings['wdtModalFontColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtModalBackgroundColor'])){ ?>
/** modals background color */
.wdt-frontend-modal .modal-dialog .modal-content,
.wpDataTables .picker__box,
.picker__list-item {
	background-color: <?php echo $wdtFontColorSettings['wdtModalBackgroundColor'] ?> !important;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtOverlayColor'])){ ?>
/** overlays background color */
<?php
	list($overlayR,$overlayG,$overlayB) = array_map('hexdec',str_split(ltrim($wdtFontColorSettings['wdtOverlayColor'],'#'),2));
?>
.modal-backdrop.in,
.wpDataTablesWrapper .picker--opened .picker__holder {
	background-color: rgba(<?php echo (int)$overlayR ?>,<?php echo (int)$overlayG ?>,<?php echo (int)$overlayB ?>,0.8) !important;
}
<?php } ?>
<?php if( get_option('wdtRenderFilter') == 'header')  { ?>
.wpDataTablesWrapper table.wpDataTable thead tr:nth-child(2) th {
	overflow: visible;
}
<?php } ?>
<?php if(!empty($wdtFontColorSettings['wdtTableFont'])){ ?>
/* table font color */
.wpDataTablesWrapper table.wpDataTable {
    font-family: <?php echo $wdtFontColorSettings['wdtTableFont'] ?> !important;
}
<?php } ?>
<?php if( !empty($wdtFontColorSettings['wdtFontSize'] ) ) { ?>
/* table font size */
.wpDataTablesWrapper table.wpDataTable {
    font-size:<?php echo $wdtFontColorSettings['wdtFontSize'] ?>px;
}
 <?php } ?>
</style>
