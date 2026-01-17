<?php
use App\Models\ErroLog;
use App\Models\RecordLog;

function __convert_value_bd($valor){
	if(str_contains((string)$valor, ".") && str_contains((string)$valor, ",")){
		$valor = str_replace('.', '', $valor);
	}
	return str_replace(",", ".", $valor);
}

function __moeda($valor, $casas_decimais = 2){
	return number_format((float)$valor, $casas_decimais, ',', '.');
}

function __moedaInput($valor, $casas_decimais = 2){
	$valor = $valor ?? 0;
	return number_format((float)$valor, $casas_decimais, ',', '');
}

function __mask($val, $mask)
{
    $maskared = '';
    $k = 0;
    for ($i = 0; $i <= strlen($mask) - 1; ++$i) {
        if ($mask[$i] == '#') {
            if (isset($val[$k])) {
                $maskared .= $val[$k++];
            }
        } else {
            if (isset($mask[$i])) {
                $maskared .= $mask[$i];
            }
        }
    }

    return $maskared;
}

function __setMask($doc)
{
    $doc = preg_replace('/[^0-9]/', '', $doc);
    $mask = '##.###.###/####-##';
    if (strlen($doc) == 11) {
        $mask = '###.###.###-##';
    }
    return __mask($doc, $mask);
}

function __mask_email($email) {
    $parts = explode('@', $email);
    $username = $parts[0];
    $domain = $parts[1] ?? '';

    return substr($username, 0, 3) . '***' . substr($username, -2) . '@' . $domain;
}

function __mask_phone($phone) {
    if (empty($phone)) return '';

    $digits = preg_replace('/[^0-9]/', '', $phone);

    return '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2, 1) . '****' . substr($digits, -3);
}
//  function __qtd_carga($valor, $casas_decimais = 4){
//  	return number_format($valor, $casas_decimais, ',', '.');
//  }

function __estoque($valor, $casas_decimais = 0){
	return number_format($valor, $casas_decimais, ',', '.');
}

function __data_pt($data, $hora = true){
	if($hora){
		return \Carbon\Carbon::parse($data)->format('d/m/Y H:i');
	}else{
		return \Carbon\Carbon::parse($data)->format('d/m/Y');
	}
}

function __valida_objeto($objeto){
	$usr = session('user_logged');
	if(!isset($objeto['empresa_id'])){
		return true;
	}
	if($objeto['empresa_id'] == $usr['empresa']){
		return true;
	}else{
		return false;
	}
}

function __array_select2($data){
	$r = [];
	foreach($data as $d){
		$r[$d] = $d;
	}
	return $r;
}

function __saveLogError($error, $empresa_id){
	ErroLog::create([
		'arquivo' => $error->getFile(),
		'linha' => $error->getLine(),
		'erro' => $error->getMessage(),
		'empresa_id' => $empresa_id
	]);
}

function __saveLog($record){
	RecordLog::create($record);
}

function erroFull($e){
	return [
		'file' => $e->getFile(),
		'line' => $e->getLine(),
		'message' => $e->getMessage(),
	];
}
