<table id="workers_table">
    <tbody>
        <tr class="info_row">
            <td id="add_worker" style="width: 27%;"><img src="{$path_to_view|default: ""}/img/add.png"> Запустить</td>
            <td id="stop_workers" style="width: 15%;"><img src="{$path_to_view|default: ""}/img/stop.png"> Стоп все</td>
            <td id="stop_one_worker" style="width: 27%;"><img src="{$path_to_view|default: ""}/img/stop.png"> Остановить воркер:</td>
            <td id="reset_queue" title="Сброс всей очереди, останов всех обработчиков" style="width: 15%;">
                <img src="{$path_to_view|default: ""}/img/delete.png"> Полный сброс</td>
        </tr>
        <tr>
            <td>
                <select id="worker_for_start">
                    <option disabled="disabled" selected="selected" value="0">Выберите воркер</option>
                    <option value="1">Все</option>
                    {foreach $workers_file_name as $worker}
                        <option>{$worker}</option>
                    {foreachelse}
                    {/foreach}
                </select>

            </td>
            <td></td>
            <td >
                <select id="worker_for_stop">
                    <option disabled="disabled" selected="selected" value="0">Выберите воркер</option>
                    <option value="1">Все</option>
                    {foreach $workers_file_name as $worker}
                        <option>{$worker}</option>
                    {foreachelse}
                    {/foreach}
                </select>
            </td>
            <td></td>
        </tr>
    </tbody>
</table>