<?php

namespace app\service;

class ProComponents
{
    /**
     * 解析字段Schema
     */
    public function parseFieldSchema(string $dataIndex, string $title, string $valueType = 'text', array $props = [])
    {
        $schema = [
            'title' => $title,
            'dataIndex' => strpos($dataIndex, '.') ? explode('.', $dataIndex) : $dataIndex,
            'valueType' => $valueType ?: 'text',
        ];
        isset($props['tip']) && !empty($props['tip']) && $schema['tooltip'] = $props['tip'];
        isset($props['template_text']) && !empty($props['template_text']) && $schema['templateText'] = $props['template_text'];
        isset($props['template_link_to']) && !empty($props['template_link_to']) && $schema['templateLinkTo'] = $props['template_link_to'];
        if (isset($props['value_enum_rel']) && !empty($props['value_enum_rel'])) {
            if ($props['value_enum_rel'][0] == 'suggest') {
                $schema['requestTable'] = $props['value_enum_rel'][1];
                //$schema['fieldProps']['debounceTime'] = 800;
                $schema['fieldProps']['showSearch'] = true;
            } else {
                $schema['valueEnum'] = system_col_rel_kv($props['value_enum_rel']);
            }
        }
        isset($props['width']) && $props['width'] > 0 && $schema['width'] = $props['width'];
        isset($props['col_size']) && $props['col_size'] > 1 && $schema['colSize'] = $props['col_size'];
        isset($props['filters']) && $props['filters'] && $schema['filters'] = true;
        isset($props['sorter']) && $props['sorter'] && $schema['sorter'] = true;
        isset($props['ellipsis']) && $props['ellipsis'] && $schema['ellipsis'] = true;
        isset($props['copyable']) && $props['copyable'] && $schema['copyable'] = true;
        isset($props['hide_in_form']) && $props['hide_in_form'] && $schema['hideInForm'] = true;
        isset($props['hide_in_table']) && $props['hide_in_table'] && $schema['hideInTable'] = true;
        isset($props['hide_in_search']) && $props['hide_in_search'] && $schema['hideInSearch'] = true;
        isset($props['hide_in_descriptions']) && $props['hide_in_descriptions'] && $schema['hideInDescriptions'] = true;
        // 合并组件属性
        isset($props['component_props']) && !empty(json_decode($props['component_props'], true)) && $schema['fieldProps'] = array_merge($schema['fieldProps'] ?? [], json_decode($props['component_props'], true));
        // unset无意义
        if (empty($schema['tooltip'])) {
            unset($schema['tooltip']);
        }
        return $schema;
    }
}
