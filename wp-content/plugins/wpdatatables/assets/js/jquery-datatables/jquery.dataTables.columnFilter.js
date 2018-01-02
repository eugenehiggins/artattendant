/*
 * File:        jquery.dataTables.columnFilter.js
 * Version:     1.5.0.
 * Author:      Jovan Popovic
 *
 * Copyright 2011-2012 Jovan Popovic, all rights reserved.
 *
 * This source file is free software, under either the GPL v2 license or a
 * BSD style license, as supplied with this software.
 *
 * This source file is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * Parameters:"
 * @sPlaceHolder                 String      Place where inline filtering function should be placed ("tfoot", "thead:before", "thead:after"). Default is "tfoot"
 * @sRangeSeparator              String      Separator that will be used when range values are sent to the server-side. Default value is "~".
 * @sRangeFormat                 string      Default format of the From ... to ... range inputs. Default is From {from} to {to}
 * @aoColumns                    Array       Array of the filter settings that will be applied on the columns
 */
(function ($) {
    $.fn.columnFilter = function (options) {

        var asInitVals, i, label, th;

        //var sTableId = "table";
        var sRangeFormat = wpdatatables_frontend_strings.from + " {from} " + wpdatatables_frontend_strings.to + " {to}";
        //Array of the functions that will override sSearch_ parameters
        var afnSearch_ = new Array();
        var aiCustomSearch_Indexes = new Array();

        var oFunctionTimeout = null;

        var fnOnFiltered = function () {
        };

        function _fnGetColumnValues(oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty) {
            ///<summary>
            ///Return values in the column
            ///</summary>
            ///<param name="oSettings" type="Object">DataTables settings</param>
            ///<param name="iColumn" type="int">Id of the column</param>
            ///<param name="bUnique" type="bool">Return only distinct values</param>
            ///<param name="bFiltered" type="bool">Return values only from the filtered rows</param>
            ///<param name="bIgnoreEmpty" type="bool">Ignore empty cells</param>

            // check that we have a column id
            if (typeof iColumn == "undefined") return new Array();

            // by default we only wany unique data
            if (typeof bUnique == "undefined") bUnique = true;

            // by default we do want to only look at filtered data
            if (typeof bFiltered == "undefined") bFiltered = true;

            // by default we do not wany to include empty values
            if (typeof bIgnoreEmpty == "undefined") bIgnoreEmpty = true;

            // list of rows which we're going to loop through
            var aiRows;

            // use only filtered rows
            if (bFiltered == true) aiRows = oSettings.aiDisplay;
            // use all rows
            else aiRows = oSettings.aiDisplayMaster; // all row numbers

            // set up data array	
            var asResultData = new Array();

            for (var i = 0, c = aiRows.length; i < c; i++) {
                var iRow = aiRows[i];
                var aData = oTable.fnGetData(iRow);
                var sValue = aData[iColumn];

                // ignore empty values?
                if (bIgnoreEmpty == true && sValue.length == 0) continue;

                // ignore unique values?
                else if (bUnique == true && $.inArray(sValue, asResultData) > -1) continue;

                // else push the value onto the result data array
                else asResultData.push(sValue);
            }

            return asResultData.sort();
        }

        function _fnColumnIndex(iColumnIndex) {
            if (properties.bUseColVis)
                return iColumnIndex;
            else
                return oTable.fnSettings().oApi._fnVisibleToColumnIndex(oTable.fnSettings(), iColumnIndex);
            //return iColumnIndex;
            //return oTable.fnSettings().oApi._fnColumnIndexToVisible(oTable.fnSettings(), iColumnIndex);
        }

        function fnCreateInput(oTable, regex, smart, bIsNumber, aoColumn) {
            var serverSide = oTable.fnSettings().oFeatures.bServerSide;
            var sCSSClass = "text_filter";
            if (bIsNumber)
                sCSSClass = "number_filter";

            label = label.replace(/(^\s*)|(\s*$)/g, "");
            var currentFilter = oTable.fnSettings().aoPreSearchCols[i].sSearch;
            var search_init = 'search_init ';
            var inputValue = label;
            if (currentFilter != '' && currentFilter != '^') {
                if (bIsNumber && currentFilter.charAt(0) == '^')
                    inputValue = currentFilter.substr(1); //ignore trailing ^
                else
                    inputValue = currentFilter;
                search_init = '';
            }

            inputValue = aoColumn.filterLabel ? aoColumn.filterLabel : inputValue;

            var input = $('<input type="text" class="' + search_init + 'form-control ' + sCSSClass + '" placeholder="' + inputValue + '" />');
            if (aoColumn.iMaxLenght != undefined && aoColumn.iMaxLenght != -1) {
                input.attr('maxlength', aoColumn.iMaxLenght);
            }
            th.html(input);
            if (bIsNumber)
                th.wrapInner('<span class="filter_column filter_number" />');
            else
                th.wrapInner('<span class="filter_column filter_text" />');

            asInitVals[i] = label;
            var index = i;

            input.keyup(function (e) {
                if (oTable.fnSettings().oFeatures.bServerSide && aoColumn.iFilterLength != 0) {
                    //If filter length is set in the server-side processing mode
                    //Check has the user entered at least iFilterLength new characters

                    var iLastFilterLength = $(this).data("dt-iLastFilterLength");
                    if (typeof iLastFilterLength == "undefined")
                        iLastFilterLength = 0;
                    var iCurrentFilterLength = this.value.length;
                    if (Math.abs(iCurrentFilterLength - iLastFilterLength) < aoColumn.iFilterLength) {
                        //Cancel the filtering
                        return;
                    }
                    else {
                        //Remember the current filter length
                        $(this).data("dt-iLastFilterLength", iCurrentFilterLength);
                    }
                }
                /* Filter on the column (the index) of this element */
                if (e.keyCode == '37' || e.keyCode == '38' || e.keyCode == '39' || e.keyCode == '40' || e.keyCode == '16') {
                    return;
                }

                var search = '';
                if (aoColumn.exactFiltering) {
                    search = serverSide ? this.value : "^" + this.value + "$";
                    oTable.fnFilter(this.value ? search : '', _fnColumnIndex(index), true, false)
                } else {
                    search = bIsNumber && !serverSide ? '^' + this.value : this.value;
                    oTable.fnFilter(search, _fnColumnIndex(index), regex, smart);
                }

                fnOnFiltered();
            });

            input.focus(function () {
                if ($(this).hasClass("search_init")) {
                    $(this).removeClass("search_init");
                    this.value = "";
                }
            });
            input.blur(function () {
                if (this.value == "") {
                    $(this).addClass("search_init");
                }
            });

            if (aoColumn.defaultValue != '') {
                var defaultValue = $.isArray(aoColumn.defaultValue) ?
                    aoColumn.defaultValue[0] : aoColumn.defaultValue;
                $(input).val(defaultValue);
                $(input).keyup();
            }

        }

        function fnCreateRangeInput(oTable, defaultValue) {

            var fromDefaultValue = '', toDefaultValue = '';

            if (defaultValue != '') {
                if ($.isArray(defaultValue)) {
                    fromDefaultValue = defaultValue[0];
                    if (defaultValue[1]) {
                        toDefaultValue = defaultValue[1];
                    }
                } else {
                    fromDefaultValue = defaultValue[0];
                }
            }

            th.html('');
            var sFromId = oTable.attr("id") + '_range_from_' + i;
            var from = $('<input type="text" class="form-control number-range-filter" id="' + sFromId + '" rel="' + i + '" placeholder="' + wpdatatables_frontend_strings.from + '" />');
            th.append(from);
            var sToId = oTable.attr("id") + '_range_to_' + i;
            var to = $('<input type="text" class="form-control number-range-filter" id="' + sToId + '" rel="' + i + '" placeholder="' + wpdatatables_frontend_strings.to + '" />');
            th.append(to);
            th.wrapInner('<span class="filter_column filter_number_range" />');
            var index = i;
            aiCustomSearch_Indexes.push(i);

            //------------start range filtering function


            /* 	Custom filtering function which will filter data in column four between two values
             *	Author: 	Allan Jardine, Modified by Jovan Popovic
             */
            //$.fn.dataTableExt.afnFiltering.push(
            oTable.dataTableExt.afnFiltering.push(
                function (oSettings, aData, iDataIndex) {
                    if (oTable.attr("id") != oSettings.sTableId)
                        return true;

                    var table_description = $.parseJSON($('#' + oTable.data('described-by')).val());
                    var number_format = (typeof table_description.number_format !== 'undefined') ? parseInt(table_description.number_format) : 1;
                    // Try to handle missing nodes more gracefully
                    if (document.getElementById(sFromId) == null)
                        return true;

                    var replace_format = number_format == 1 ? /\./g : /,/g;

                    var iMin = document.getElementById(sFromId).value.replace(replace_format, '');
                    var iMax = document.getElementById(sToId).value.replace(replace_format, '');
                    var iValue = aData[_fnColumnIndex(index)] == "-" ? '0' : aData[_fnColumnIndex(index)].replace(replace_format, '');
                    if (number_format == 1) {
                        iMin = iMin.replace(/,/g, '.');
                        iMax = iMax.replace(/,/g, '.');
                        iValue = iValue.replace(/,/g, '.');
                    }
                    if (iMin !== '') {
                        iMin = iMin * 1;
                    }
                    if (iMax !== '') {
                        iMax = iMax * 1;
                    }
                    iValue = iValue * 1;
                    if (iMin === "" && iMax === "") {
                        return true;
                    }
                    else if (iMin === "" && iValue <= iMax) {
                        return true;
                    }
                    else if (iMin <= iValue && "" === iMax) {
                        return true;
                    }
                    else if (iMin <= iValue && iValue <= iMax) {
                        return true;
                    }
                    return false;
                }
            );
            //------------end range filtering function


            $('#' + sFromId + ',#' + sToId, th).keyup(function () {

                var iMin = document.getElementById(sFromId).value * 1;
                var iMax = document.getElementById(sToId).value * 1;
                if (iMin != 0 && iMax != 0 && iMin > iMax)
                    return;

                oTable.fnDraw();
                fnOnFiltered();
            });

            if (fromDefaultValue != '') {
                $(from).val(fromDefaultValue);
                $(document).ready(function () {
                    $(from).keyup();
                });
            }
            if (toDefaultValue != '') {
                $(to).val(toDefaultValue);
                $(document).ready(function () {
                    $(to).keyup();
                });
            }

        }

        function fnCreateDateRangeInput(oTable, defaultValue) {


            var fromDefaultValue = '', toDefaultValue = '';

            if (defaultValue != '') {
                if ($.isArray(defaultValue)) {
                    fromDefaultValue = defaultValue[0];
                    if (defaultValue[1]) {
                        toDefaultValue = defaultValue[1];
                    }
                } else {
                    fromDefaultValue = defaultValue[0];
                }
            }


            var aoFragments = sRangeFormat.split(/[}{]/);

            var descriptionContainer = $.parseJSON($('#' + oTable.data('described-by')).val());
            var dateFormat = descriptionContainer.datepickFormat.replace(/y/g, 'yy').replace(/Y/g, 'yyyy').replace(/M/g, 'mmm');

            th.html("");
            //th.html(_fnRangeLabelPart(0));
            var sFromId = oTable.attr("id") + '_range_from_' + i;
            var from = $('<input type="text" class="form-control date-range-filter wdt-datepicker" id="' + sFromId + '" rel="' + i + '" placeholder="' + wpdatatables_frontend_strings.from + '" />');
            //th.append(from);
            //th.append(_fnRangeLabelPart(1));

            var sToId = oTable.attr("id") + '_range_to_' + i;
            var to = $('<input type="text" class="form-control date-range-filter wdt-datepicker" id="' + sToId + '" rel="' + i + '" placeholder="' + wpdatatables_frontend_strings.to + '" />');
            //th.append(to);
            //th.append(_fnRangeLabelPart(2));

            for (ti = 0; ti < aoFragments.length; ti++) {
                if (aoFragments[ti] == properties.sDateFromToken) {
                    th.append(from);
                } else {
                    if (aoFragments[ti] == properties.sDateToToken) {
                        th.append(to);
                    } else {
//                        th.append(aoFragments[ti]);
                    }
                }
            }
            th.wrapInner('<span class="filter_column filter_date_range" />');

            var index = i;
            aiCustomSearch_Indexes.push(i);


            //------------start date range filtering function

            //$.fn.dataTableExt.afnFiltering.push(
            oTable.dataTableExt.afnFiltering.push(
                function (oSettings, aData, iDataIndex) {
                    if (oTable.attr("id") != oSettings.sTableId)
                        return true;

                    var dStartDate = from.val() != '' ? new Date(from.val()) : from.val();
                    var dEndDate = to.val() != '' ? new Date(to.val()) : to.val();

                    if (dStartDate == '' && dEndDate == '') {
                        return true;
                    }

                    var dCellDate = null;
                    try {
                        if (aData[_fnColumnIndex(index)] == null || aData[_fnColumnIndex(index)] == "")
                            return false;
                        dCellDate = new Date(aData[_fnColumnIndex(index)]);
                    } catch (ex) {
                        return false;
                    }
                    if (dCellDate == null)
                        return false;


                    if (dStartDate == '' && dCellDate <= dEndDate) {
                        return true;
                    }
                    else if (dStartDate <= dCellDate && dEndDate == '') {
                        return true;
                    }
                    else if (dStartDate <= dCellDate && dCellDate <= dEndDate) {
                        return true;
                    }
                    return false;
                }
            );
            //------------end date range filtering function

            $('#' + sFromId + ',#' + sToId, th).blur(function () {
                oTable.fnDraw();
                fnOnFiltered();
            });

            $('#' + sFromId + ',#' + sToId, th).change(function () {
                oTable.fnDraw();
                fnOnFiltered();
            });

            if (fromDefaultValue != '') {
                $(from).val(fromDefaultValue);
                $(document).ready(function () {
                    $(from).change();
                });
            }
            if (toDefaultValue != '') {
                $(to).val(toDefaultValue);
                $(document).ready(function () {
                    $(to).change();
                });
            }

        }

        function fnCreateDateTimeRangeInput(oTable, defaultValue) {


            var fromDefaultValue = '', toDefaultValue = '';

            if (defaultValue != '') {
                if ($.isArray(defaultValue)) {
                    fromDefaultValue = defaultValue[0];
                    if (defaultValue[1]) {
                        toDefaultValue = defaultValue[1];
                    }
                } else {
                    fromDefaultValue = defaultValue[0];
                }
            }


            var aoFragments = sRangeFormat.split(/[}{]/);

            var descriptionContainer = $.parseJSON($('#' + oTable.data('described-by')).val());
            var dateFormat = descriptionContainer.datepickFormat.replace(/y/g, 'yy').replace(/Y/g, 'yyyy').replace(/M/g, 'mmm');
            var timeFormat = descriptionContainer.timeFormat.replace('H', 'HH');
            var momentTimeFormat = timeFormat.replace('i', 'mm');

            th.html("");
            //th.html(_fnRangeLabelPart(0));
            var sFromId = oTable.attr("id") + '_range_from_' + i;
            var fromHTML = '<input type="text" class="form-control date-time-range-filter wdt-datetimepicker" id="' + sFromId + '" rel="' + i + '" placeholder="' + wpdatatables_frontend_strings.from + '" />';
            //fromHTML += '<div style="display: none !important;"><input type="text" id="'+sFromId+'_date" class="date" rel="'+i+'"/>';
            //fromHTML += '<input type="text" id="'+sFromId+'_time" class="time" rel="'+i+'"/></div>';

            var from = $(fromHTML);

            var sToId = oTable.attr("id") + '_range_to_' + i;
            var toHTML = '<input type="text" class="form-control date-time-range-filter wdt-datetimepicker" id="' + sToId + '" rel="' + i + '" placeholder="' + wpdatatables_frontend_strings.to + '" />';
            //toHTML += '<div style="display: none !important;"><input type="text" id="'+sToId+'_date" class="date" rel="'+i+'"/>';
            //toHTML += '<input type="text" id="'+sToId+'_time" class="time" rel="'+i+'"/></div>';

            var to = $(toHTML);

            for (ti = 0; ti < aoFragments.length; ti++) {
                if (aoFragments[ti] == properties.sDateFromToken) {
                    th.append(from);
                } else {
                    if (aoFragments[ti] == properties.sDateToToken) {
                        th.append(to);
                    }
                }
            }
            th.wrapInner('<span class="filter_column filter_date_range" />');

            var index = i;
            aiCustomSearch_Indexes.push(i);

            //------------start date range filtering function

            oTable.dataTableExt.afnFiltering.push(
                function (oSettings, aData, iDataIndex) {
                    if (oTable.attr("id") != oSettings.sTableId)
                        return true;

                    var dStartDate = moment(from.val(), dateFormat.toUpperCase() + ' ' + momentTimeFormat).toDate();
                    var dEndDate = moment(to.val(), dateFormat.toUpperCase() + ' ' + momentTimeFormat).toDate();

                    if (dStartDate == 'Invalid Date' && dEndDate == 'Invalid Date') {
                        return true;
                    }

                    var dCellDate = null;
                    try {
                        if (aData[_fnColumnIndex(index)] == null || aData[_fnColumnIndex(index)] == "")
                            return false;
                        dCellDate = moment(aData[_fnColumnIndex(index)], dateFormat.toUpperCase() + ' ' + momentTimeFormat).toDate();
                    } catch (ex) {
                        return false;
                    }
                    if (dCellDate == 'Invalid Date')
                        return false;


                    if (dStartDate == 'Invalid Date' && dCellDate <= dEndDate) {
                        return true;
                    }
                    else if (dStartDate <= dCellDate && dEndDate == 'Invalid Date') {
                        return true;
                    }
                    else if (dStartDate <= dCellDate && dCellDate <= dEndDate) {
                        return true;
                    }
                    return false;
                }
            );
            //------------end date range filtering function
            $('#' + sFromId + ',#' + sToId, th).blur(function () {
                oTable.fnDraw();
                fnOnFiltered();
            });

            $('#' + sFromId + ',#' + sToId, th).change(function () {
                oTable.fnDraw();
                fnOnFiltered();
            });

            if (fromDefaultValue != '') {
                $(from).val(fromDefaultValue);
                $(document).ready(function () {
                    $(from).change();
                });
            }
            if (toDefaultValue != '') {
                $(to).val(toDefaultValue);
                $(document).ready(function () {
                    $(to).change();
                });
            }

        }

        function fnCreateTimeRangeInput(oTable, defaultValue) {


            var fromDefaultValue = '', toDefaultValue = '';

            if (defaultValue != '') {
                if ($.isArray(defaultValue)) {
                    fromDefaultValue = defaultValue[0];
                    if (defaultValue[1]) {
                        toDefaultValue = defaultValue[1];
                    }
                } else {
                    fromDefaultValue = defaultValue[0];
                }
            }


            var aoFragments = sRangeFormat.split(/[}{]/);

            var descriptionContainer = $.parseJSON($('#' + oTable.data('described-by')).val());
            var dateFormat = descriptionContainer.datepickFormat.replace(/y/g, 'yy').replace(/Y/g, 'yyyy').replace(/M/g, 'mmm');
            var timeFormat = descriptionContainer.timeFormat.replace('H', 'HH');
            var momentTimeFormat = timeFormat.replace('i', 'mm');

            th.html("");
            var sFromId = oTable.attr("id") + '_range_from_' + i;
            var fromHTML = '<input type="text" class="form-control time-range-filter wdt-timepicker" id="' + sFromId + '" rel="' + i + '" placeholder="' + wpdatatables_frontend_strings.from + '" />';

            var from = $(fromHTML);

            var sToId = oTable.attr("id") + '_range_to_' + i;
            var toHTML = '<input type="text" class="form-control rime-range-filter wdt-timepicker" id="' + sToId + '" rel="' + i + '" placeholder="' + wpdatatables_frontend_strings.to + '" />';

            var to = $(toHTML);

            for (ti = 0; ti < aoFragments.length; ti++) {
                if (aoFragments[ti] == properties.sDateFromToken) {
                    th.append(from);
                } else {
                    if (aoFragments[ti] == properties.sDateToToken) {
                        th.append(to);
                    }
                }
            }
            th.wrapInner('<span class="filter_column filter_date_range" />');

            var index = i;
            aiCustomSearch_Indexes.push(i);

            //------------start date range filtering function

            oTable.dataTableExt.afnFiltering.push(
                function (oSettings, aData, iDataIndex) {
                    if (oTable.attr("id") != oSettings.sTableId)
                        return true;

                    var dStartTime = moment(from.val(), momentTimeFormat).toDate();
                    var dEndTime = moment(to.val(), momentTimeFormat).toDate();

                    if (dStartTime == 'Invalid Date' && dEndTime == 'Invalid Date') {
                        return true;
                    }

                    var dCellTime = null;
                    try {
                        if (aData[_fnColumnIndex(index)] == null || aData[_fnColumnIndex(index)] == "")
                            return false;
                        dCellTime = moment(aData[_fnColumnIndex(index)], momentTimeFormat).toDate();
                    } catch (ex) {
                        return false;
                    }
                    if (dCellTime == 'Invalid Date')
                        return false;


                    if (dStartTime == 'Invalid Date' && dCellTime <= dEndTime) {
                        return true;
                    }
                    else if (dStartTime <= dCellTime && dEndTime == 'Invalid Date') {
                        return true;
                    }
                    else if (dStartTime <= dCellTime && dCellTime <= dEndTime) {
                        return true;
                    }
                    return false;
                }
            );
            //------------end date range filtering function
            $('#' + sFromId + ',#' + sToId, th).blur(function () {
                oTable.fnDraw();
                fnOnFiltered();
            });

            $('#' + sFromId + ',#' + sToId, th).change(function () {
                oTable.fnDraw();
                fnOnFiltered();
            });

            if (fromDefaultValue != '') {
                $(from).val(fromDefaultValue);
                $(document).ready(function () {
                    $(from).change();
                });
            }
            if (toDefaultValue != '') {
                $(to).val(toDefaultValue);
                $(document).ready(function () {
                    $(to).change();
                });
            }

        }


        function fnCreateColumnSelect(oTable, aoColumn, iColumn, nTh, sLabel) {
            var serverSide = oTable.fnSettings().oFeatures.bServerSide;
            if (aoColumn.values == null)
                aoColumn.values = _fnGetColumnValues(oTable.fnSettings(), iColumn, true, false, true);
            if (aoColumn.possibleValuesAddEmpty == true && !serverSide) {
                aoColumn.values.unshift('possibleValuesAddEmpty');
            }
            var index = iColumn;
            var currentFilter = oTable.fnSettings().aoPreSearchCols[i].sSearch;
            if (currentFilter == null || currentFilter == "")//Issue 81
                currentFilter = aoColumn.selected;

            if (aoColumn.defaultValue != '') {
                if ($.isArray(aoColumn.defaultValue)) {
                    aoColumn.defaultValue = aoColumn.defaultValue[0];
                }
            }

            sLabel = aoColumn.filterLabel ? aoColumn.filterLabel : sLabel;

            var r = '<select class="search_init select_filter selectpicker" data-index="' + iColumn + '"><option value="" class="search_init">' + ' ' + '</option>';
            var j = 0;
            var iLen = aoColumn.values.length;
            for (j = 0; j < iLen; j++) {
                if (typeof (aoColumn.values[j]) != 'object') {
                    var selected = '';
                    if (encodeURI(aoColumn.values[j]) == currentFilter
                        || encodeURI(aoColumn.values[j]) == encodeURI(currentFilter)
                    )
                        selected = 'selected ';

                    if ((aoColumn.defaultValue != '') && (encodeURI(aoColumn.values[j]) == encodeURI(aoColumn.defaultValue))) {
                        selected = 'selected= "selected" ';
                    }

                    var optionLabel = aoColumn.values[j];
                    if (aoColumn.values[j] == 'possibleValuesAddEmpty') {
                        optionLabel = ' ';
                    }

                    r += '<option ' + selected + ' value="' + encodeURI(aoColumn.values[j]) + '">' + optionLabel + '</option>';
                }
                else {
                    var selected = '';
                    if (aoColumn.bRegex) {
                        //Do not escape values if they are explicitely set to avoid escaping special characters in the regexp
                        if (aoColumn.values[j].value == currentFilter) {
                            selected = 'selected= "selected" ';
                        }

                        if ((aoColumn.defaultValue != '') && (aoColumn.values[j].value == aoColumn.defaultValue)) {
                            selected = 'selected= "selected" ';
                        }
                        r += '<option ' + selected + 'value="' + aoColumn.values[j].value + '">' + aoColumn.values[j].label + '</option>';
                    } else {
                        if (encodeURI(aoColumn.values[j].value) == currentFilter) selected = 'selected ';
                        if ((aoColumn.defaultValue != '') && (aoColumn.values[j].value == aoColumn.defaultValue)) {
                            selected = 'selected= "selected" ';
                        }
                        r += '<option ' + selected + 'value="' + encodeURI(aoColumn.values[j].value) + '">' + aoColumn.values[j].label + '</option>';
                    }
                }
            }

            var select = $(r + '</select>');
            nTh.html(select);
            nTh.wrapInner('<span class="filter_column filter_select" />');
            select.change(function () {

                if ($(this).val() != "") {
                    $(this).removeClass("search_init");
                } else {
                    $(this).addClass("search_init");
                }

                var search = '';
                if ($(this).val() == 'possibleValuesAddEmpty' && !serverSide) {
                    oTable.fnFilter('^$', iColumn, true, false);
                } else {
                    if (aoColumn.exactFiltering) {
                        search = serverSide ? decodeURIComponent($(this).val()) : '^' + decodeURIComponent($(this).val()) + '$';
                        oTable.fnFilter($(this).val() ? search : '', iColumn, true, false);
                    } else {
                        if (aoColumn.bRegex)
                            oTable.fnFilter($(this).val(), iColumn, aoColumn.bRegex);
                        else
                            oTable.fnFilter(decodeURIComponent($(this).val()), iColumn);
                    }
                }

                fnOnFiltered();
            });

            if (currentFilter != null && currentFilter != "")
                oTable.fnFilter(unescape(currentFilter), iColumn);

            if (aoColumn.defaultValue != '')
                select.change();

            $('.selectpicker[data-index=' + iColumn + ']').selectpicker('refresh');
        }

        function fnCreateSelect(oTable, aoColumn) {

            fnCreateColumnSelect(oTable, aoColumn, _fnColumnIndex(i), th, label);

        }

        function fnCreateCheckbox(oTable, aoColumn) {

            if (!$.isArray(aoColumn.defaultValue)) {
                aoColumn.defaultValue = [aoColumn.defaultValue];
            }

            if (aoColumn.values == null)
                aoColumn.values = _fnGetColumnValues(oTable.fnSettings(), i, true, true, true);
            var index = i;

            var r = '', j, iLen = aoColumn.values.length;

            var dialogRender = true;

            if (typeof aoColumn.sSelector !== 'undefined') {
                dialogRender = !!($(aoColumn.sSelector).is('td') || $(aoColumn.sSelector).is('th'));
            }

            //clean the string
            var localLabel = label.replace('%', 'Perc').replace("&", "AND").replace("$", "DOL").replace("£", "STERL").replace("@", "AT").replace(/\s/g, "_").replace(' ', '_');
            localLabel = localLabel.replace(/[^a-zA-Z 0-9]+/g, '');
            var localGroupLabel = wdtRandString();

            //clean the string

            //button label override
            var labelBtn = aoColumn.filterLabel ? aoColumn.filterLabel : label;

            var relativeDivWidthToggleSize = 10;
            var numRow = 12; //numero di checkbox per colonna
            var numCol = Math.floor(iLen / numRow);
            if (iLen % numRow > 0) {
                numCol = numCol + 1;
            }

            //count how many column should be generated and split the div size
            var divWidth = 100 / numCol - 2;

            var divWidthToggle = relativeDivWidthToggleSize * numCol;

            if (numCol == 1) {
                divWidth = 20;
            }

            var divRowDef = '<div style="min-width: ' + divWidth + '%; " >';
            var divClose = '</div>';

            if (dialogRender) {
                var uniqueId = oTable.attr("id") + localLabel;
                var buttonId = "chkBtnOpen" + uniqueId;
                var checkToggleDiv = uniqueId + "-flt-toggle";
                r += '<button id="' + buttonId + '" class="checkbox-filter btn" > ' + labelBtn + '</button>'; //filter button which opens the dialog
                r += '<div id="' + checkToggleDiv + '" '
                    + 'title="' + label + '" '
                    + 'class="toggle-check"  >'; //dialog div
                r += divRowDef;
            }

            for (j = 0; j < iLen; j++) {

                localLabel = wdtRandString();

                //if last check close div
                if (j % numRow == 0 && j != 0) {
                    //r += divClose + divRowDef;
                }

                //check button
                var checked = '';
                if (typeof aoColumn.values[j] != 'object') {
                    checked = $.inArray(aoColumn.values[j].toString(), aoColumn.defaultValue) != -1 ? 'checked="checked" ' : '';
                } else {
                    checked = $.inArray(aoColumn.values[j].value.toString(), aoColumn.defaultValue) != -1 ? 'checked="checked" ' : '';
                }

                if (typeof (aoColumn.values[j]) != 'object') {
                    r += '<div class="wdt_checkbox_option checkbox"><label><input type="checkbox" class="search_init checkbox-filter wdtFilterCheckbox" id= "' + localLabel + '" name= "' + localGroupLabel + '" value=\'' + aoColumn.values[j] + '\' ' + checked + '><i class="input-helper"></i>' + aoColumn.values[j] + '</label></div>';
                } else {
                    r += '<div class="wdt_checkbox_option checkbox"><label><input type="checkbox" class="search_init checkbox-filter wdtFilterCheckbox" id= "' + localLabel + '" name= "' + localGroupLabel + '" value=\'' + aoColumn.values[j].value + '\' ' + checked + '><i class="input-helper"></i>' + aoColumn.values[j].label + '</label></div>';
                }


                //on every checkbox selection
                $(document).on('change', '#' + localLabel, function () {

                    var search = '';
                    var or = '|'; //var for select checks in 'or' into the regex

                    if (dialogRender) {
                        var checkboxInputs =
                            $(this).closest('.modal-content').find('input:checkbox[name="' + localGroupLabel + '"]:checked');
                    } else {
                        checkboxInputs = $('input:checkbox[name="' + localGroupLabel + '"]:checked');
                    }

                    var resSize = checkboxInputs.size();

                    checkboxInputs.each(function (index) {

                        //concatenation for selected checks in or
                        if ((index == 0 && resSize == 1)
                            || (index != 0 && index == resSize - 1)) {
                            or = '';
                        }
                        //trim
                        search = search.replace(/^\s+|\s+$/g, "");

                        //search = search + ' ' + $(this).val();
                        //search = search + '(?=.*' + $(this).val().replace(/\+/g,'\\+') + ')';
                        if (aoColumn.exactFiltering) {
                            search = search + '^' + $(this).val().replace(/\+/g, '\\+') + '$' + or;
                        } else {
                            search = search + $(this).val().replace(/\+/g, '\\+') + or;
                        }

                        or = '|';

                    });

                    for (var jj = 0; jj < iLen; jj++) {
                        if (search != "") {
                            $('#' + localLabel).removeClass("search_init");
                        } else {
                            $('#' + localLabel).addClass("search_init");
                        }
                    }

                    //execute search
                    oTable.fnFilter(search, index, true, false);
                    fnOnFiltered();
                });

            }

            th.html(r);

            th.wrapInner('<span class="filter_column filter_checkbox" />');

            $(document).ready(function () {
                for (var ind in aoColumn.defaultValue) {
                    $('input.wdtFilterCheckbox[value="' + aoColumn.defaultValue[ind] + '"]').change();
                }
            });

            if (dialogRender) {
                var dlg = $('#' + checkToggleDiv).wrap('<div class="wdtCheckboxModalWrap" />').hide();

                $('#wdt-frontend-modal').on('click', '#wdt-checkbox-filter-close', function (e) {
                    e.preventDefault();
                    $('#wdt-frontend-modal').modal('hide');
                    if ($(this).closest('.modal-content').find('#' + checkToggleDiv).length > 0)
                        $('#' + checkToggleDiv).html($(this).closest('.modal-content').find('#' + checkToggleDiv));
                });

                $('#wdt-frontend-modal').on('click', '#wdt-checkbox-filter-reset', function (e) {
                    e.preventDefault();
                    if ($(this).closest('.modal-content').find('#' + checkToggleDiv).length > 0)
                        $(this).closest('.modal-content').find($('input:checkbox[name="' + localGroupLabel + '"]:checked')).each(function () {
                            $(this).attr('checked', false).change();
                            $(this).addClass("search_init");
                        });

                    oTable.fnFilter('', index, true, false);
                    fnOnFiltered();
                });

                $('#' + buttonId).click(function (e) {
                    e.preventDefault();
                    $('#wdt-frontend-modal .modal-title').html(labelBtn);
                    $('#wdt-frontend-modal .modal-body').html(dlg.clone().show());
                    $('#wdt-frontend-modal .modal-footer').html('<button class="btn btn-danger btn-icon-text waves-effect" id="wdt-checkbox-filter-reset" href="#">Reset</button><button class="btn btn-success btn-icon-text waves-effect" id="wdt-checkbox-filter-close" href="#"><i class="zmdi zmdi-check"></i>OK</button>');
                    $('#wdt-frontend-modal').modal('show');
                });
            }

        }

        var oTable = this;

        var defaults = {
            sPlaceHolder: "foot",
            sRangeSeparator: "~",
            iFilteringDelay: 500,
            aoColumns: null,
            sRangeFormat: "From {from} to {to}",
            sDateFromToken: "from",
            sDateToToken: "to"
        };

        var properties = $.extend(defaults, options);

        return this.each(function () {

            if (!oTable.fnSettings().oFeatures.bFilter)
                return;
            asInitVals = new Array();

            var aoFilterCells = oTable.fnSettings().aoFooter[0];

            var oHost = oTable.fnSettings().nTFoot; //Before fix for ColVis
            var sFilterRow = "tr"; //Before fix for ColVis

            if (properties.sPlaceHolder == "head:after") {
                var tr = $("tr:first", oTable.fnSettings().nTHead).detach();
                //tr.appendTo($(oTable.fnSettings().nTHead));
                if (oTable.fnSettings().bSortCellsTop) {
                    tr.prependTo($(oTable.fnSettings().nTHead));
                    //tr.appendTo($("thead", oTable));
                    aoFilterCells = oTable.fnSettings().aoHeader[1];
                }
                else {
                    tr.appendTo($(oTable.fnSettings().nTHead));
                    //tr.prependTo($("thead", oTable));
                    aoFilterCells = oTable.fnSettings().aoHeader[0];
                }

                sFilterRow = "tr:last";
                oHost = oTable.fnSettings().nTHead;

            } else if (properties.sPlaceHolder == "head:before") {

                if (oTable.fnSettings().bSortCellsTop) {
                    var tr = $("tr:first", oTable.fnSettings().nTHead).detach();
                    tr.appendTo($(oTable.fnSettings().nTHead));
                    aoFilterCells = oTable.fnSettings().aoHeader[1];
                } else {
                    var tr = $("tr:first", oTable.fnSettings().nTHead).detach();
                    tr.appendTo($(oTable.fnSettings().nTHead));
                    aoFilterCells = oTable.fnSettings().aoHeader[0];
                }
                /*else {
                 //tr.prependTo($("thead", oTable));
                 sFilterRow = "tr:first";
                 }*/

                sFilterRow = "tr:first";

                oHost = oTable.fnSettings().nTHead;


            }

            //$(sFilterRow + " th", oHost).each(function (index) {//bug with ColVis
            $(aoFilterCells).each(function (index) {//fix for ColVis
                i = index;
                var aoColumn = {
                    type: "text",
                    bRegex: false,
                    bSmart: true,
                    iMaxLenght: -1,
                    iFilterLength: 0
                };
                if (properties.aoColumns != null) {
                    if (properties.aoColumns.length < i || properties.aoColumns[i] == null)
                        return;
                    aoColumn = properties.aoColumns[i];
                }
                //label = $(this).text(); //Before fix for ColVis
                label = $($(this)[0].cell).text(); //Fix for ColVis
                $($(this)[0].cell).addClass('column-' + aoColumn.origHeader.toString().toLowerCase().replace(/\ /g, '-'));
                if (aoColumn.sSelector == null) {
                    //th = $($(this)[0]);//Before fix for ColVis
                    th = $($(this)[0].cell); //Fix for ColVis
                }
                else {
                    th = $(aoColumn.sSelector);
                    if (th.length == 0) {
                        th = $($(this)[0].cell);
                    }
                }
                if (aoColumn != null) {
                    if (aoColumn.sRangeFormat != null)
                        sRangeFormat = aoColumn.sRangeFormat;
                    else
                        sRangeFormat = properties.sRangeFormat;
                    switch (aoColumn.type) {
                        case "null":
                            break;
                        case "number":
                            fnCreateInput(oTable, true, false, true, aoColumn);
                            break;
                        case "select":
                            if (aoColumn.bRegex != true)
                                aoColumn.bRegex = false;
                            fnCreateSelect(oTable, aoColumn);
                            break;
                        case "number-range":
                            fnCreateRangeInput(oTable, aoColumn.defaultValue);
                            break;
                        case "date-range":
                            fnCreateDateRangeInput(oTable, aoColumn.defaultValue);
                            break;
                        case "datetime-range":
                            fnCreateDateTimeRangeInput(oTable, aoColumn.defaultValue);
                            break;
                        case "time-range":
                            fnCreateTimeRangeInput(oTable, aoColumn.defaultValue);
                            break;
                        case "checkbox":
                            fnCreateCheckbox(oTable, aoColumn);
                            break;
                        case "text":
                        default:
                            bRegex = (aoColumn.bRegex == null ? false : aoColumn.bRegex);
                            bSmart = (aoColumn.bSmart == null ? false : aoColumn.bSmart);
                            fnCreateInput(oTable, bRegex, bSmart, false, aoColumn);
                            break;

                    }
                }
            });

            for (j = 0; j < aiCustomSearch_Indexes.length; j++) {
                //var index = aiCustomSearch_Indexes[j];
                var fnSearch_ = function () {
                    var id = oTable.attr("id");
                    if ((typeof $("#" + id + "_range_from_" + aiCustomSearch_Indexes[j]).val() === 'undefined')
                        || (typeof $("#" + id + "_range_to_" + aiCustomSearch_Indexes[j]).val() === 'undefined')) {
                        return properties.sRangeSeparator;
                    }
                    return $("#" + id + "_range_from_" + aiCustomSearch_Indexes[j]).val() + properties.sRangeSeparator + $("#" + id + "_range_to_" + aiCustomSearch_Indexes[j]).val();
                }
                afnSearch_.push(fnSearch_);
            }

            if (oTable.fnSettings().oFeatures.bServerSide) {
                var fnServerDataOriginal = oTable.fnSettings().fnServerData;

                if (typeof oTable.fnSettings().ajax.data !== 'undefined') {
                    var currentDataMethod = oTable.fnSettings().ajax.data;
                }

                oTable.fnSettings().ajax = {
                    url: oTable.fnSettings().ajax.url,
                    type: 'POST',
                    data: function (d) {
                        if (typeof currentDataMethod !== 'undefined') {
                            currentDataMethod(d);
                        }
                        for (j = 0; j < aiCustomSearch_Indexes.length; j++) {
                            var index = aiCustomSearch_Indexes[j];
                            d.columns[index].search.value = afnSearch_[j]();
                        }
                        d.sRangeSeparator = properties.sRangeSeparator;
                    }
                };

                if (fnServerDataOriginal != null) {
                    try {
                        fnServerDataOriginal(sSource, aoData, fnCallback, oTable.fnSettings()); //TODO: See Issue 18
                    } catch (ex) {
                        fnServerDataOriginal(sSource, aoData, fnCallback);
                    }
                }

            }

        });

    };

})(jQuery);
