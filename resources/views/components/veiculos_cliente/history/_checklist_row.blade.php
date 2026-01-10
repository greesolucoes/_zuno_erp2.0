<tr>
    <td class="text-left">{{ $ch->id }}</td>
    <td class="text-left">{{ ($ch->ordem_id && $ch->ordem_id > 0) ? $ch->ordem_id : '-'}}</td>
    <td class="text-left">{{ __data_pt($ch->created_at, 0)}}</td>
    <td class="text-left">{{ $ch->status_checklist_label }}</td>
    <td class="text-left">{{ $ch->funcionario->nome }}</td>
</tr>
