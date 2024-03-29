<?php

// para teste
//$remessa = 202401;


// Pega dados do usuário
echo "Conversão do CSV da MSC.", PHP_EOL;
echo "Digite os dados solicitados.", PHP_EOL;

echo "Ano [AAAA]: ";
fscanf(STDIN, "%d\n", $ano);
echo "Mês [MM] (1 ~ 13): ";
fscanf(STDIN, "%d\n", $mes);
$mes = str_pad($mes, 2, '0', STR_PAD_LEFT);

$remessa = (int) $ano.$mes;

// Configurações
$msc_output_base_dir = 'Z:\\MSC\\';


// Prepara o ambiente
require 'vendor/autoload.php';

$con = connect('host=localhost port=5432 dbname=pmidd user=postgres password=lise890');
echo 'Conectado ao banco de dados'.PHP_EOL;

$output_file_path = parse_remessa_to_filepath($msc_output_base_dir, $remessa);
echo "Carregando dados de $output_file_path".PHP_EOL;
$data = load_source_csv($output_file_path);
printf('Linhas carregadas: %d'.PHP_EOL, sizeof($data));


echo 'Convertendo linhas...'.PHP_EOL;
$progressBar = new \NickBeen\ProgressBar\ProgressBar(maxProgress: sizeof($data));
$progressBar->start();
$msc = [];
foreach ($data as $line) {
    $row = line_to_row($line);
    $msc[] = $row;
    $progressBar->tick();
}
$progressBar->finish();
printf('Linhas convertidas: %d'.PHP_EOL, sizeof($msc));


echo 'Escrevendo tabela temporária...'.PHP_EOL;
begin_transaction($con);
echo 'Transação iniciada.'.PHP_EOL;

echo 'Limpando tabela temporária...'.PHP_EOL;
if (!pg_query($con, "DELETE FROM tmp.msc")) {
    $error = pg_last_error($con);
    trigger_error("Falha ao limpar tmp.msc: {$error}", E_USER_ERROR);
}

echo 'Escrevendo dados na tabela temporária...'.PHP_EOL;
$progressBar = new \NickBeen\ProgressBar\ProgressBar(maxProgress: sizeof($msc));
$progressBar->start();
foreach ($msc as $row) {
    write_tmp($con, $row);
    $progressBar->tick();
}
$progressBar->finish();

echo 'Salvando dados temporários...'.PHP_EOL;
commit($con);

echo 'Consolidando valores...'.PHP_EOL;
$msc = consolida_dados($con);
printf('Linhas consolidadas: %d'.PHP_EOL, sizeof($msc));
//print_r($msc);exit();

begin_transaction($con);
echo 'Transação iniciada.'.PHP_EOL;
$progressBar = new \NickBeen\ProgressBar\ProgressBar(maxProgress: sizeof($msc));
$progressBar->start();
foreach ($msc as $row) {
    write_row($con, $row);
    $progressBar->tick();
}
$progressBar->finish();

echo 'Finalizando remessa...'.PHP_EOL;
finalize($con, $remessa);

echo 'Salvando dados...'.PHP_EOL;
commit($con);

echo 'Processo terminado!'.PHP_EOL;