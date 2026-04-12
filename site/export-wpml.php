<?php
global $wpdb;

$strings = $wpdb->get_results("
    SELECT s.id, s.context, s.name, s.value
    FROM {$wpdb->prefix}icl_strings s
    ORDER BY s.context, s.name
");

$fp = fopen(__DIR__ . "/wpml_strings_all.csv", "w");
fputcsv($fp, ["id", "domain", "name", "source"]);

foreach ($strings as $s) {
    fputcsv($fp, [
        $s->id,
        $s->context,
        $s->name,
        $s->value
    ]);
}

fclose($fp);

echo "Exported " . count($strings) . " strings\n";

