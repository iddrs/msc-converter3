<?php

function load_source_csv(string $filePath): array {
    if(!file_exists($filePath)) trigger_error ("Arquivo $filePath não encontrado.", E_USER_ERROR);
    
    $data = file($filePath, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    if($data === false) trigger_error ("Não foi possível ler dados de $filePath");
    
    array_shift($data);//remove a primeira linha, pois não dos dados que interessa
    array_shift($data);//remove a linha de cabeçalho
    
    return $data;
}

function parse_remessa_to_filepath(string $mscOutputBaseDir, int $remessa): string {
    $mes = (int) substr($remessa, -1, 2);
    $ano = substr($remessa, 0, 4);
    
    $meses = [
        1 => 'MSCAgregadaJaneiro',
        2 => 'MSCAgregadaFevereiro',
        3 => 'MSCAgregadaMarco',
        4 => 'MSCAgregadaAbril',
        5 => 'MSCAgregadaMaio',
        6 => 'MSCAgregadaJunho',
        7 => 'MSCAgregadaJulho',
        8 => 'MSCAgregadaAgosto',
        9 => 'MSCAgregadaSetembro',
        10 => 'MSCAgregadaOutubro',
        11 => 'MSCAgregadaNovembro',
        12 => 'MSCAgregadaDezembro',
        13 => 'MSCEncerramento',
    ];
    
    return sprintf("%s%s\\%s%s.csv", $mscOutputBaseDir, $ano, $meses[$mes], $ano);
}