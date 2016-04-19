<table id="controls_table">
    <tbody>
        <tr class="info_row">
            <td id="add_worker"><button><img src="{$path_to_view|default: ""}/img/add.png"><span class="button_title">Go!</span></button></td>
            <td id="stop_workers"><button><img src="{$path_to_view|default: ""}/img/stop.png"><span class="button_title">Stop all</span></button></td>
            <td id="reset_queue" title="Сброс всей очереди, останов всех обработчиков">
                <button><img src="{$path_to_view|default: ""}/img/delete.png"><span class="button_title"> Total reset</span></button></td>
        </tr>
        <tr>
            <td>
                <select id="worker_for_start">
                    <option disabled="disabled" selected="selected" value="0">Select worker</option>
                    <option value="1">All</option>
                    {foreach $workers_file_name as $worker}
                        <option>{$worker}</option>
                    {foreachelse}
                    {/foreach}
                </select>

            </td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>