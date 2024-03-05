<?php

function write_row(\PgSql\Connection $con, array $row): void {
    if (!pg_insert($con, 'msc.msc', $row)) {
        $error = pg_last_error($con);
        var_dump($row);
        trigger_error("Falha ao inserir dados: {$error}", E_USER_ERROR);
    }
}

function write_tmp(\PgSql\Connection $con, array $row): void {
    if (!pg_insert($con, 'tmp.msc', $row)) {
        $error = pg_last_error($con);
        var_dump($row);
        trigger_error("Falha ao inserir dados: {$error}", E_USER_ERROR);
    }
}
