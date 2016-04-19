{foreach $rows as $log_row}
    <tr class="{$log_row.odd_class} row_log">
        <td class="log_row_time">
            {$log_row.ctime}<br><span class="gdate">{$log_row.date}</span>
        </td>
        <td class="log_row_msg">
            {$log_row.log_msg}
        </td>
    </tr>
{foreachelse}
{/foreach}
