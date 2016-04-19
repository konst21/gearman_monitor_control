/**
 * Update interval is set in Gmonitor_Settings.php,
 * transfer via hidden div
 */
function update_interval(){
    return $('#interval').text();
}

/**
 * Active workers count
 */
function check_workers(){
    setInterval(function(){
        $.ajax({
            url: 'ajax/count_workers.php',
            data: 'random='+Math.random(),
            type: 'GET',
            success: function(html_resp){
                $('#count_workers').html(html_resp);
                stop_one_worker();
            }
        });
    }, update_interval());
}

/**
 * Add worker :)
 */
function add_worker(){
    $('#add_worker').click(function(){
            var worker_file_name = $('select#worker_for_start').val();
            if(worker_file_name == '0'){
                jAlert('Please select the worker');
                return;
            }
            jPrompt('How much workers you want to add?', 1, null, function(num){
                //если нажали Cansel в окне, num = null
                if(num){
                    if(worker_file_name != '0'){
                        $.ajax({
                            url: 'ajax/start_workers.php',
                            data: 'random=' + Math.random() + '&number=' + num + '&worker_file_name=' + worker_file_name,
                            type: 'GET',
                            success: function(msg){
                                jAlert(msg);
                            }
                        })
                    }
                }
            });
/*
            $.ajax({
                url: 'ajax/start_workers.php',
                data: 'random='+Math.random(),
                type: 'GET',
                success: function(msg){
                    jAlert(msg);
                }
            })
*/
        }
    )
}

/**
 * Queue reset via fake worker
 */
function reset_queue(){
    $('#reset_queue').click(function(){
        jConfirm("All tasks will canceled!\n Reset?", "Queue Reset",function(r){
            if(r){
                $.ajax({
                url: 'ajax/reset_queue.php',
                data: 'random='+Math.random(),
                type: 'GET',
                success: function(msg){
                    jAlert(msg);
                    }
                })
            }
        })
    }
   )
}

/**
 * Stop all workers
 */
function stop_workers(){
    $('#stop_workers').click(function(){
        $.ajax({
            url: 'ajax/stop_workers.php',
            data: 'random='+Math.random(),
            type: 'GET',
            success: function(msg){
                jAlert(msg);
            }
        })
    }
    )
}

/**
 * Check functions registered on job server
 * getting and display task statuses with task reset button
 */
function current_func_status(){
    setInterval(function(){
    $.ajax({
        url: 'ajax/functions_status.php',
        data: 'random='+Math.random(),
        dataType: 'JSON',
        success: function(json){
            $('#functions_progress').html('');
            $.each(json, function(key, val){

            var row = '<tr>' +
                    '<td class="gearman_func_name">' + val.func_name + '</td>' +
                    '<td class="gearman_func_value">' + val.in_queue + '</td>' +
                    '<td class="gearman_func_value">' + val.jobs_running + '</td>' +
                    '<td class="gearman_func_value">' + val.capable_workers + '</td>' +
                    '<td class="reset_task" style="background: url(view/img/delete.png) no-repeat center"></td>'+
                    '</tr>';
            $('#functions_progress').append(row);
            })
            $('.reset_task').bind('click', function(){
                var func_name = $(this).parent().children('td.gearman_func_name').text();
                $.ajax({
                    url: 'ajax/reset_task.php',
                    type: 'GET',
                    data: 'function_name='+func_name,
                    success: function(response){
                        jAlert(response);
                    }
                });
            })


        }
    })
    }, update_interval());
}



/**
 * Adding color for log row when mouse hover
 */
function log_td_on_mouse(){
    var row_td = $('.row_log');
    row_td.mouseover(function(){
        $(this).addClass('row_on_mouse');
    });
    row_td.mouseleave(function(){
        $(this).removeClass('row_on_mouse');
    });
}

/**
 * Adding color for control elements when mouse hover
 */
function workers_td_on_mouse(){
    var worker_td = $('#workers_table tr.info_row td');
    worker_td.mouseover(function(){
        $(this).addClass('td_on_mouse');
    });
    worker_td.mouseleave(function(){
        $(this).removeClass('td_on_mouse');
    });
}

/**
 * View and refresh log
 */
function view_log(){
    setInterval(function(){
        var search_text = $('input#log_search').val();
        var search_confirm = $('input#log_search_confirm').prop('checked');
        if(search_confirm && search_text){
            if(search_text != $.cookie('search_text')){
                $.ajax({
                    url: 'ajax/log_search.php',
                    data: 'search_text='+search_text,
                    success: function(resp){
                        $('#first_log_row').after(resp);
                        log_td_on_mouse();
                        view_log_msg();
                    }
                })
            }
        }
        else{
            $.ajax({
                url: 'ajax/log_change.php',
                data: 'random='+Math.random()+'&search='+search_confirm+'&search_text='+search_text,
                success: function(resp){
                    $('#first_log_row').after(resp);
                    log_td_on_mouse();
                    view_log_msg();

                }
            })
        }


    }, update_interval());
}

/**
 * Display log event in separate window when click
 */
function view_log_msg(){
    var msg_td = $('.log_row_msg');
    msg_td.click(function(){
        var msg = $(this).text();
        jAlert(msg);
    });
}

/**
 * Stop selected worker
 */
function stop_one_worker(){
    $('.reset_worker').click(function(){
        var worker_file_name = $(this).parent().find('.worker_file_name').text();
        if(!worker_file_name){
            jAlert('Worker not find');
            return
        }
        else{
            $.ajax({
                url: 'ajax/stop_workers.php',
                data: 'random='+Math.random()+'&worker_file_name='+worker_file_name,
                type: 'GET',
                success: function(msg){
                    jAlert(msg);
                }
            })

        }
    })
}



$(document).ready(function(){
    check_workers();
    current_func_status();
    add_worker();
    stop_workers();
    view_log();
    reset_queue();
    workers_td_on_mouse();
    stop_one_worker();
})
