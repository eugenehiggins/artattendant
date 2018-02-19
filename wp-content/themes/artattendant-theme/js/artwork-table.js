function actionFormatter(value, row, index) {

    return [
        ' <strong>' + value + '</strong><br/><span class="action-links  btn-group  btn-group-sm">',
        /*
                '<a class="view" href="/discover/?post_type=download&p='+row['id']+'" title="View">',
                '<i class="fa fa-search fa-lg"></i>',
                '</a>',
        */
        '<a class="preview btn btn-default btn-sm"  href="/collection/?task=preview&post_id=' + row['id'] + '" title="Preview">',
        '<i class="fa fa-picture-o fa-lg"></i>',
        '</a>',
        '<a class="edit btn btn-default btn-sm" href="/collection/?task=edit-product&post_id=' + row['id'] + '" title="Edit">',
        '<i class="fa fa-edit fa-lg"></i>',
        '</a>',
        '<a class="remove btn btn-default btn-sm" href="/collection/?task=delete-product&post_id=' + row['id'] + '" title="Remove">',
        '<i class="fa fa-remove fa-lg"></i>',
        '</a></span>'
        /*
                 '<a class="remove ml10" href="javascript:void('+row['id']+')" title="Remove">',
                '<i class="fa fa-remove"></i>',
                '</a>'
        */

    ].join('');
}


function populateFormatter(data) {
    /*
            var total = 0;
            jQuery.each(data, function (i, row) {
                total += +(row.edd_price.substring(1));
            });
            return '$' + total;
    */
}


function totalTextFormatter(data) {
    return 'Total';
}

function totalNameFormatter(data) {
    return data.length;
}

function detailFormatter(index, row) {
    var html = [];
    html.push('<p><b>I am extra data ... I want to be seems when table is shrinked to CarDView');
    jQuery.each(row, function (key, value) {
        html.push('<p><b>' + key + ':</b> ' + value + '</p>');
    });
    return html.join('');
}


/*
    function operateFormatter(value, row, index) {
        return [
            '<a class="like" href="javascript:void(0)" title="Like">',
            '<i class="glyphicon glyphicon-heart"></i>',
            '</a>  ',
            '<a class="remove" href="javascript:void(0)" title="Remove">',
            '<i class="glyphicon glyphicon-remove"></i>',
            '</a>'
        ].join('');
    }

    window.operateEvents = {
        'click .like': function (e, value, row, index) {
            alert('You click like action, row: ' + JSON.stringify(row));
        },
        'click .remove': function (e, value, row, index) {
            $table.bootstrapTable('remove', {
                field: 'id',
                values: [row.id]
            });
        }
    };
*/


(function ($) {

    var $table = $('#fes-product-list');
    $table.on('load-success.bs.table', function (data) {
        $tableRows = $table.find('tbody tr');
        tableData = $table.bootstrapTable('getData', true);
        $.each(tableData, function (i, row) {
            if ('archive' == row.status) {
                $tableRows.eq(i).find('.editable').editable('toggleDisabled');
            }
        });
    });


    /*
    $(function () {
        var $table = $('#fes-product-list');
        $table.on('editable-init.bs.table', function () {

            $table.find('.editable').editable('disable');
        });
    });
    */
    /*
        $('#fes-product-list').on('editable-save.bs.table', function(e, field, row, oldValue, $el){
            console.log('field',field);
            //console.log('row',row);
            //console.log('oldValue',oldValue);
            //console.log(row[field]);
            //console.log(row[id]);
            //console.log('$el',$el);
           var data = {
                action: 'anagram_table_artwork',
                id: row['id'],
                field: field,
                value: row[field]
            };
            jQuery.post(ajaxurl, data, function(response) {
                // whatever you need to do; maybe nothing
                console.log(response);
            });

        });
    */


    window.actionEvents = {
        'click .view': function (e, value, row, index) {
            // alert('You click view icon, row: ' + JSON.stringify(row));
            // console.log(row['id']);
            // console.log(value, row, index);
        },
        'click .edit': function (e, value, row, index) {
            // alert('You click edit icon, row: ' + JSON.stringify(row));
            // console.log(value, row, index);
        },
        'click .remove': function (e, value, row, index) {
            //alert('You click remove icon, row: ' + JSON.stringify(row));
            // console.log(value, row, index);
        }
    };


    /*
    $('#enable').click(function() {
        $(this).closest('td').find('a').editable('toggleDisabled');
    });
    */


    $('#fes-product-list').on('load-success.bs.table', function (e, text) {

        // console.log('test');
        /*
               $('#marketsReport').find('.city').each(function(){
                   $(this).addClass($(this).text());
                });
        */
    });


    /*

        BootstrapTable.prototype.initTable = function () {
            var that = this;
            _initTable.apply(this, Array.prototype.slice.apply(arguments));

            if (!this.options.editable) {
                return;
            }

            $.each(this.columns, function (i, column) {
                if (!column.editable) {
                    return;
                }

                console.log('bob');


                var editableOptions = {},
                    editableDataMarkup = [],
                    editableDataPrefix = 'editable-';

                var processDataOptions = function (key, value) {
                    // Replace camel case with dashes.
                    var dashKey = key.replace(/([A-Z])/g, function ($1) {
                        return "-" + $1.toLowerCase();
                    });
                    if (dashKey.slice(0, editableDataPrefix.length) == editableDataPrefix) {
                        var dataKey = dashKey.replace(editableDataPrefix, 'data-');
                        editableOptions[dataKey] = value;
                    }
                };



            });
        }
    */


    window.icons = {
        paginationSwift: 'fa-collapse-up',
        refresh: 'fa-refresh',
        toggle: 'fa-table',
        print: 'fa-print',
        //columns: 'fa-th-list',
        //paginationSwitchDown: 'glyphicon-collapse-down icon-chevron-down',
        //paginationSwitchUp: 'glyphicon-collapse-up icon-chevron-up',
        //detailOpen: 'glyphicon-plus icon-plus',
        //detailClose: 'glyphicon-minus icon-minus'
    };


    var $table = jQuery('#fes-product-list'),
        $remove = jQuery('#remove'),
        selections = [];


    //* Currently unused*//
    function responseHandler(res) {
        // console.log(res);
        return res;
    }

    /*
    Edit/adjust https://github.com/wenzhixin/bootstrap-table-examples/blob/master/welcome.html
        http://jsfiddle.net/zafvbuwq/
        */

    /*
    $table.on('expand-row.bs.table', function (e, index, row, $detail) {
        $detail.html('Loading from ajax request...');
        $.get('LICENSE', function (res) {
            $detail.html(res.replace(/\n/g, '<br>'));
        });
    });
    */
    /*
    $table.on('getCookies.bs.table', function (e, index, row, $detail) {

        console.log('hi');
    });
    */
//$('#table').bootstrapTable('method', parameter);

    //$.fn.bootstrapTable.utils.setCookie(console.log('bob'));
    //$.fn.bootstrapTable.utils.setCookie($('#table').data("bootstrap.table"), 'bs.table.sortName', 'name');


    $table.bootstrapTable('destroy').bootstrapTable({
        //data: data,
        idField: 'id',
        toggle: "table",
        responseHandler: responseHandler,
        stickyHeader: true,
        /*
                    fixedColumns: true,
                    fixedNumber: 2,
        */
        //stickyHeaderOffsetY: stickyHeaderOffsetY + 'px',
        cookie: true,
        showToggleBtn: false, //Use this to hide and show all columns that are 'switchable'.
        showPrint: false,
        // advancedSearch: true,
        // filterControl: true,
        cookieIdTable: 'artworktableID3',
        cookieStorage: 'localStorage',
        search: true,
        minimumCountColumns: 0,
        iconsPrefix: 'fa',
        icons: "icons",
        pageList: "[10, 25, 50, 100, ALL]",
        showRefresh: false,
        mobileResponsive: true,
        showColumns: true,
        showFooter: false,
        striped: true,
        sortName: 'title',
        sortOrder: 'desc',
        hover: true,
        clickToSelect: true,
        singleSelect: true,
        pagination: true,
        showToggle: true,
        smartDisplay: true,
        cardView: true,
        // columnsHidden: ['status', 'location','cost','edd_price'], //mobile hidden
        // detailView: true,
        // detailFormatter: detailFormatter,
        //toolbar: "#toolbar",
        // editableUrl : "./edit.php",
        //sidePagination: 'server', // use this if you want to have search etc form the server
        url: "/wp-json/artattendant_api/v2/products/?user=" + artwork_ajax_vars.user_id + "&per_page=2000",
        formatNoMatches: function () {
            return 'There are no artworks to display';
        },
        showExport: false,

        //exportTypes: "['excel', 'pdf']",
        /*
                     exportOptions: '{
                         "fileName": "artwork_export",
                         "worksheetName": "test1",
                         "jspdf": {
                           "autotable": {
                             "styles": { "rowHeight": 20, "fontSize": 10 },
                             "headerStyles": { "fillColor": 255, "textColor": 0 },
                             "alternateRowStyles": { "fillColor": [60, 69, 79], "textColor": 255 }
                           }
                         }
                       }',
        */
        columns: [
            /*
                                    {
                            field: 'action',
                            title: 'Actions',
                            formatter: 'actionFormatter',
                            events: 'actionEvents',
                            cardVisible: false,
                            switchable: false,
                            printIgnore: true

                        },
            */
            {
                field: 'image',
                title: 'Image',
                switchable: false,
                class: 'table-image',
                //align: 'center',
                /*
                                success : function(response, newValue) {
                                    updateAccount(this.id, newValue);
                                }
                */
            },
            {
                field: 'artist',
                title: 'Artist',
                formatter: 'actionFormatter',
                events: 'actionEvents',
                switchable: false,
                cardVisible: false,
                sortable: true,

                /*
                                editable: {
                                    type: 'text',
                                    url: ajaxurl,
                                    ajaxOptions: {
                                        type: 'post',
                                        //dataType: 'json',
                                        //contentType: 'application/json'
                                    },
                                    send: 'always',
                                    params: function(params) {
                                           var data = {
                                            action: 'anagram_table_artwork',
                                            id: params.pk,
                                            field: params.name,
                                            value: params.value,
                                            type: 'artist'
                                        };

                                        return data;
                                    },
                                    success: function(response, newValue) {
                                        console.log(response);
                                        if(!response) {
                                            return "Unknown error!";
                                        }

                                        if(response.success === false) {
                                             return response.msg;
                                        }

                                    },
                                 },
                */

            },
            {
                field: 'title',
                title: 'Title',
                switchable: false,
                cardVisible: false,
                sortable: true,
                //footerFormatter: totalNameFormatter,
                editable: {
                    type: 'text',
                    url: artwork_ajax_vars.ajaxurl,
                    ajaxOptions: {
                        type: 'post',
                        //dataType: 'json',
                        //contentType: 'application/json'
                    },
                    send: 'always',
                    params: function (params) {
                        var data = {
                            action: 'anagram_table_artwork',
                            id: params.pk,
                            field: params.name,
                            value: params.value,
                            type: 'title'
                        };

                        return data;
                    },
                    success: function (response, newValue) {
                        console.log(response);
                        if (!response) {
                            return "Unknown error!";
                        }

                        if (response.success === false) {
                            return response.msg;
                        }

                    },
                },
            },
            {
                field: 'status',
                title: 'Status',
                align: 'center',
                cardVisible: false,
                sortable: true,
                printIgnore: true,
                editable: {
                    type: 'select',
                    noedit: function (value, row, index) {
                        console.log(row);
                        if (row.archive == 'y') {
                            return false;  // return false if you want the field editable.
                        } else {
                            return '<span class="fa fa-ban" title="single people have no spouse."></span>';
                        }
                    },
                    url: artwork_ajax_vars.ajaxurl,
                    //disabled: true,
                    ajaxOptions: {
                        type: 'post',
                        //dataType: 'json',
                        //contentType: 'application/json'
                    },
                    send: 'always',
                    params: function (params) {
                        var data = {
                            action: 'anagram_table_artwork',
                            id: params.pk,
                            field: params.name,
                            value: params.value,
                            type: 'status'
                        };

                        return data;
                    },
                    success: function (response, newValue) {
                        console.log(response);
                        if (!response) {
                            return "Unknown error!";
                        }

                        if (response.success === false) {
                            return response.msg;
                        }

                    },
                    prepend: "Select a Status",
                    source: [
                        {value: 'publish', text: 'Public'},
                        {value: 'private', text: 'Private'},
                        {value: 'archive', text: 'Sold', disabled: true},

                    ],
                    display: function (value, sourceData) {
                        if (value == 'private') {
                            jQuery(this).html('<i class="fa fa-eye-slash fa-lg" aria-hidden="true"></i>').css("color", "#ec971f");

                        } else if (value == 'publish') {
                            jQuery(this).html('<i class="fa fa-eye fa-lg" aria-hidden="true"></i>').css("color", "#5cb85c");
                        } else {
                            jQuery(this).html('<i class="fa fa-eye fa-lg" aria-hidden="true"></i>');
                        }
                    },
                }

            },
            {
                field: 'cost',
                title: 'Cost',
                sortable: true,
                cardVisible: false,
                align: 'center',
                editable: {
                    type: 'text',
                    emptytext: "0",
                    url: artwork_ajax_vars.ajaxurl,
                    ajaxOptions: {
                        type: 'post',
                        //dataType: 'json',
                        //contentType: 'application/json'
                    },
                    send: 'always',
                    params: function (params) {
                        var data = {
                            action: 'anagram_table_artwork',
                            id: params.pk,
                            field: params.name,
                            value: params.value,
                            type: 'amount'
                        };

                        return data;
                    },
                    success: function (response, newValue) {

                        if (!response) {
                            return "Unknown error!";
                        }

                        if (response.success === false) {
                            return response.msg;
                        }

                        if (response.success === true) {
                            //console.log(response);
                            // var totalCost = accounting.unformat(jQuery('#totalCost').text());
                            //  newTotal =  parseInt(newValue) + parseInt(totalCost);
                            //console.log(accounting.formatMoney(newTotal) );
                            jQuery('#totalCost').text(response.data.costTotal);


                        }

                    },
                    display: function (value, response) {
                        //var k = value;//.format(2);
                        jQuery(this).text(accounting.formatMoney(value));
                    },
                    /*
                                        validate: function (value) {
                                            value = $.trim(value);
                                            if (!value) {
                                                return 'This field is required';
                                            }
                                            if (!/^\$/.test(value)) {
                                                return 'This field needs to start width $.'
                                            }
                                            var data = $table.bootstrapTable('getData'),
                                                index = $(this).parents('tr').data('index');
                                            console.log(data[index]);
                                            return '';
                                         }
                    */
                },
            },
            {
                field: 'edd_price',
                title: 'Price',
                sortable: true,
                cardVisible: false,
                //footerFormatter: populateFormatter,
                editable: {
                    type: 'text',
                    emptytext: "0",
                    url: artwork_ajax_vars.ajaxurl,
                    ajaxOptions: {
                        type: 'post',
                        //dataType: 'json',
                        //contentType: 'application/json'
                    },
                    send: 'always',
                    params: function (params) {
                        var data = {
                            action: 'anagram_table_artwork',
                            id: params.pk,
                            field: params.name,
                            value: params.value,
                            type: 'amount'
                        };

                        return data;
                    },
                    success: function (response, newValue) {

                        if (!response) {
                            return "Unknown error!";
                        }

                        if (response.success === false) {
                            return response.msg;
                        }

                        if (response.success === true) {

                            jQuery('#totalPrice').text(response.data.priceTotal);


                        }

                    },
                    display: function (value, response) {
                        //var k = value;//.format(2);
                        jQuery(this).text(accounting.formatMoney(value));
                    },

                },

            },
            {
                field: 'date_created',
                title: 'Year',
                sortable: true,
                cardVisible: false,
                editable: {
                    type: 'text',
                    emptytext: "",
                    url: artwork_ajax_vars.ajaxurl,
                    ajaxOptions: {
                        type: 'post',
                        //dataType: 'json',
                        //contentType: 'application/json'
                    },
                    send: 'always',
                    params: function (params) {
                        var data = {
                            action: 'anagram_table_artwork',
                            id: params.pk,
                            field: params.name,
                            value: params.value,
                            type: 'meta'
                        };

                        return data;
                    },
                    success: function (response, newValue) {
                        console.log(response);
                        if (!response) {
                            return "Unknown error!";
                        }

                        if (response.success === false) {
                            return response.msg;
                        }

                    }
                },


            },
            {
                field: 'inventory',
                title: 'Inventory',
                sortable: true,
                cardVisible: false,
                editable: {
                    type: 'text',
                    emptytext: "add",
                    url: artwork_ajax_vars.ajaxurl,
                    ajaxOptions: {
                        type: 'post',
                        //dataType: 'json',
                        //contentType: 'application/json'
                    },
                    send: 'always',
                    params: function (params) {
                        var data = {
                            action: 'anagram_table_artwork',
                            id: params.pk,
                            field: params.name,
                            value: params.value,
                            type: 'invonumber'
                        };

                        return data;
                    },
                    success: function (response, newValue) {
                        //console.log(response);
                        if (!response) {
                            return "Unknown error!";
                        }

                        if (response.success === false) {
                            //console.log(response);
                            return response.data;
                        }

                    }
                },

            },
            {
                field: 'location',
                title: 'Location',
                sortable: true,
                cardVisible: false,
                editable: {
                    type: 'text',
                    url: artwork_ajax_vars.ajaxurl,
                    ajaxOptions: {
                        type: 'post',
                        //dataType: 'json',
                        //contentType: 'application/json'
                    },
                    send: 'always',
                    params: function (params) {
                        var data = {
                            action: 'anagram_table_artwork',
                            id: params.pk,
                            field: params.name,
                            value: params.value,
                            type: 'meta'
                        };

                        return data;
                    },
                    success: function (response, newValue) {
                        console.log(response);
                        if (!response) {
                            return "Unknown error!";
                        }

                        if (response.success === false) {
                            return response.msg;
                        }

                    },
                },
                /*
                                editable: {
                                    //https://webdevstudios.com/2015/08/11/replacing-default-wordpress-user-dropdowns-ajax-solution/
                                   // type: 'select',
                                    type: 'select2',
                                  // sourceCache: false,
                                   // source:  "../load.php?drop=category",
                                    select2: {
                                        placeholder: 'Select a Location',
                                        allowClear: true,
                                        width: '230px',
                                        minimumInputLength: 3,
                                        ajax: {
                                            url:  ajaxurl,
                                            dataType: 'json',
                                            data : function (term, page) {
                                                return {
                                                    q : term,
                                                    action : 'get_member_location',
                                                    //nonce : app.nonce,
                                                };
                                            },
                                            results : function( ajax_data, page, query ) {
                                                var items=[];
                                                console.log(ajax_data);

                                                $.each( ajax_data.data, function( i, item ) {
                                                    var new_item = {
                                                        'id' : item.id,
                                                        'text' : item.text
                                                    };

                                                    items.push(new_item);
                                                });

                                                return { results: items };
                                            }

                                        },

                                    }
                */
                /* suucess not needed
                        function( data ) {

            data = $.map(data, function(item) {
                return { id: item.location, text: item.location };
            });
                 ,
               success: function(response) {
                    $('#RequestUser').text(response.newVal);
                }
                */
                // }


            },
            {
                field: 'purchased_from',
                title: 'Purchased',
                sortable: true,
                cardVisible: false,
                editable: {
                    type: 'text',
                    url: artwork_ajax_vars.ajaxurl,
                    ajaxOptions: {
                        type: 'post',
                        //dataType: 'json',
                        //contentType: 'application/json'
                    },
                    send: 'always',
                    params: function (params) {
                        var data = {
                            action: 'anagram_table_artwork',
                            id: params.pk,
                            field: params.name,
                            value: params.value,
                            type: 'meta'
                        };

                        return data;
                    },
                    success: function (response, newValue) {
                        console.log(response);
                        if (!response) {
                            return "Unknown error!";
                        }

                        if (response.success === false) {
                            return response.msg;
                        }

                    },
                },

            },
            {
                field: 'consigned_sold_to',
                title: 'Consigned/Sold',
                sortable: true,
                cardVisible: false,
                editable: {
                    type: 'text',
                    url: artwork_ajax_vars.ajaxurl,
                    ajaxOptions: {
                        type: 'post',
                        //dataType: 'json',
                        //contentType: 'application/json'
                    },
                    send: 'always',
                    params: function (params) {
                        var data = {
                            action: 'anagram_table_artwork',
                            id: params.pk,
                            field: params.name,
                            value: params.value,
                            type: 'meta'
                        };

                        return data;
                    },
                    success: function (response, newValue) {
                        console.log(response);
                        if (!response) {
                            return "Unknown error!";
                        }

                        if (response.success === false) {
                            return response.msg;
                        }

                    },
                },

            },
            {
                field: 'details',
                title: 'Details',
                visible: false,
                switchable: false

            },
            /*
                        {
                            field: 'operate',
                            title: 'Item Operate',
                            align: 'center',
                            events: operateEvents,
                            formatter: operateFormatter
                        }
            */
        ]
    });


})(jQuery);



