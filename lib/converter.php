<?php

function line_to_row(string $line): array {
    $buffer = explode(';', $line);
//    return $buffer;
//    print_r($buffer);
    $row['remessa'] = 0;
    $row['conta_contabil'] = $buffer[0];
    $row['poder_orgao'] = null;
    $row['financeiro_permanente'] = null;
    $row['divida_consolidada'] = null;
    $row['indicador_exercicio_fonte_recurso'] = null;
    $row['fonte_recurso'] = null;
    $row['codigo_acompanhamento_orcamentario'] = null;
    $row['natureza_receita'] = null;
    $row['natureza_despesa'] = null;
    $row['funcao'] = null;
    $row['subfuncao'] = null;
    $row['ano_inscricao_restos_a_pagar'] = null;
    $row['saldo_inicial'] = 0.0;
    $row['movimento_debito'] = 0.0;
    $row['movimento_credito'] = 0.0;
    $row['saldo_final'] = 0.0;

    $row = parse_ic($buffer[1], $buffer[2], $row);
    $row = parse_ic($buffer[3], $buffer[4], $row);
    $row = parse_ic($buffer[5], $buffer[6], $row);
    $row = parse_ic($buffer[7], $buffer[8], $row);
    $row = parse_ic($buffer[9], $buffer[10], $row);
    $row = parse_ic($buffer[11], $buffer[12], $row);

    switch ($buffer[14]) {
        case 'beginning_balance':
            $row['saldo_inicial'] = parse_value($row['conta_contabil'], $buffer[15], (float) $buffer[13]);
            break;
        case 'period_change':
            if ($buffer[15] == 'D') {
                $row['movimento_debito'] = round($buffer[13], 2);
            } else {
                $row['movimento_credito'] = round($buffer[13], 2);
            }
            break;
        case 'ending_balance':
            $row['saldo_final'] = parse_value($row['conta_contabil'], $buffer[15], (float) $buffer[13]);
            break;
    }

    return $row;
}

function parse_value(string $cc, string $natureza, float $valor): float {
    switch ((int) $cc[0]) {
        case 1:
        case 3:
        case 5:
        case 7:
            if ($natureza == 'D') {
                return round($valor, 2);
            } else {
                return round($valor * -1, 2);
            }
        case 2:
        case 4:
        case 6:
        case 8:
            if ($natureza == 'C') {
                return round($valor, 2);
            } else {
                return round($valor * -1, 2);
            }
    }
}

function parse_ic(string $ic, string $tipo, array $row): array {
    switch ($tipo) {
        case 'PO':
            $row['poder_orgao'] = $ic;
            break;
        case 'FP':
            $row['financeiro_permanente'] = $ic;
            break;
        case 'DC':
            $row['divida_consolidada'] = $ic;
            break;
        case 'FR':
            $row['indicador_exercicio_fonte_recurso'] = substr($ic, 0, 1);
            $row['fonte_recurso'] = substr($ic, 1);
            break;
        case 'CO':
            $row['codigo_acompanhamento_orcamentario'] = $ic;
            break;
        case 'NR':
            $row['natureza_receita'] = $ic;
            break;
        case 'ND':
            $row['natureza_despesa'] = $ic;
            break;
        case 'FS':
            $row['funcao'] = substr($ic, 0, 2);
            $row['subfuncao'] = substr($ic, 2);
            break;
        case 'AI':
            $row['ano_inscricao_restos_a_pagar'] = $ic;
            break;
        default:
            return $row;
    }
    return $row;
}

function finalize(\PgSql\Connection $con, int $remessa): void {
    if (!pg_query($con, "DELETE FROM msc.msc WHERE remessa = $remessa")) {
        $error = pg_last_error($con);
        trigger_error("Falha remover a remessa $remessa de msc.msc: {$error}", E_USER_ERROR);
    }

//    if (!pg_query($con, "DELETE FROM tmp.msc")) {
//        $error = pg_last_error($con);
//        trigger_error("Falha ao limpar tmp.msc: {$error}", E_USER_ERROR);
//    }

    if (!pg_query($con, "UPDATE msc.msc SET remessa = $remessa WHERE remessa = 0")) {
        $error = pg_last_error($con);
        trigger_error("Falha ao atualizar a remessa $remessa: {$error}", E_USER_ERROR);
    }
}

function consolida_dados(PgSql\Connection $con): array {
//    if (!pg_query($con, "DELETE FROM tmp.msc")) {
//        $error = pg_last_error($con);
//        trigger_error("Falha ao limpar tmp.msc: {$error}", E_USER_ERROR);
//    }
    
    $sql = 'SELECT REMESSA,
	CONTA_CONTABIL,
	PODER_ORGAO,
	FINANCEIRO_PERMANENTE,
	DIVIDA_CONSOLIDADA,
	INDICADOR_EXERCICIO_FONTE_RECURSO,
	FONTE_RECURSO,
	CODIGO_ACOMPANHAMENTO_ORCAMENTARIO,
	NATUREZA_RECEITA,
	NATUREZA_DESPESA,
	FUNCAO,
	SUBFUNCAO,
	ANO_INSCRICAO_RESTOS_A_PAGAR,
	SUM(SALDO_INICIAL)::DECIMAL AS SALDO_INICIAL,
	SUM(MOVIMENTO_DEBITO)::DECIMAL AS MOVIMENTO_DEBITO,
	SUM(MOVIMENTO_CREDITO)::DECIMAL AS MOVIMENTO_CREDITO,
	SUM(SALDO_FINAL)::DECIMAL AS SALDO_FINAL
FROM TMP.MSC
GROUP BY REMESSA,
	CONTA_CONTABIL,
	PODER_ORGAO,
	FINANCEIRO_PERMANENTE,
	DIVIDA_CONSOLIDADA,
	INDICADOR_EXERCICIO_FONTE_RECURSO,
	FONTE_RECURSO,
	CODIGO_ACOMPANHAMENTO_ORCAMENTARIO,
	NATUREZA_RECEITA,
	NATUREZA_DESPESA,
	FUNCAO,
	SUBFUNCAO,
	ANO_INSCRICAO_RESTOS_A_PAGAR';
    $result = pg_query($con, $sql);
    if (!$result) {
        $error = pg_last_error($con);
        trigger_error("Falha ao consolidar dados: {$error}", E_USER_ERROR);
    }
    return pg_fetch_all($result, PGSQL_ASSOC);
}
