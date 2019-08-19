<?php

function _generate_controller_file($entity_name, $entity_structs, $entity_relationships)
{/*{{{*/
    $inputs = [];
    foreach ($entity_structs as $struct) {
        $inputs[] = $struct['name'];
    }

    foreach ($entity_relationships as $relationship) {

        $relationship_type = $relationship['type'];
        $relationship_name = $relationship['relation_name'];

        if ($relationship_type !== 'has_many') {
            $inputs[] = $relationship_name.'_id';
        }
    }

    $content = _get_controller_template_from_extension('list');

    otherwise($content, '没找到 controller 的 list 模版');

    $list_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_structs' => $inputs,
    ]);

    $content = _get_controller_template_from_extension('add');

    otherwise($content, '没找到 controller 的 add 模版');

    $add_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_structs' => $inputs,
    ]);

    $content = _get_controller_template_from_extension('detail');

    otherwise($content, '没找到 controller 的 detail 模版');

    $detail_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_structs' => $inputs,
    ]);

    $content = _get_controller_template_from_extension('update');

    otherwise($content, '没找到 controller 的 update 模版');

    $update_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_structs' => $inputs,
    ]);

    $content = _get_controller_template_from_extension('delete');

    otherwise($content, '没找到 controller 的 delete 模版');

    $delete_content =  blade_eval($content, [
        'entity_name' => $entity_name,
        'entity_structs' => $inputs,
    ]);

    $template = "<?php

%s
%s
%s
%s
%s";

    $content = sprintf($template, $list_content, $add_content, $detail_content, $update_content, $delete_content);

    return str_replace('^', '', $content);
}/*}}}*/

function _generate_page($action)
{/*{{{*/
    $content = _get_page_template_from_extension($action);

    otherwise($content, '没找到 '.$action.' 的 page 模版');

    return $content;
}/*}}}*/

function _generate_struct_add($struct_type)
{/*{{{*/
    $content = _get_struct_template_from_extension('add', $struct_type);

    otherwise($content, '没找到 '.$struct_type.' 的 add 模版');

    return $content;
}/*}}}*/

function _generate_struct_detail($struct_type)
{/*{{{*/
    $content = _get_struct_template_from_extension('detail', $struct_type);

    otherwise($content, '没找到 '.$struct_type.' 的 detail 模版');

    return $content;
}/*}}}*/

function _generate_struct_update($struct_type)
{/*{{{*/
    $content = _get_struct_template_from_extension('update', $struct_type);

    otherwise($content, '没找到 '.$struct_type.' 的 update 模版');

    return $content;
}/*}}}*/

function _generate_struct_list($struct_type)
{/*{{{*/
    $content = _get_struct_template_from_extension('list', $struct_type);

    otherwise($content, '没找到 '.$struct_type.' 的 list 模版');

    return $content;
}/*}}}*/

function _generate_view_add_file($entity_name)
{/*{{{*/
    $template = _generate_page('add');

    $content = blade_eval($template, [
        'entity_name' => $entity_name,
    ]);

    return str_replace('^', '', $content);
}/*}}}*/

function _generate_view_update_file($entity_name)
{/*{{{*/
    $template = _generate_page('update');

    $content = blade_eval($template, [
        'entity_name' => $entity_name,
    ]);

    return str_replace('^', '', $content);
}/*}}}*/

function _generate_view_list_file($entity_name)
{/*{{{*/
    $template = _generate_page('list');

    $content = blade_eval($template, [
        'entity_name' => $entity_name,
    ]);

    return str_replace('^', '', $content);
}/*}}}*/

command('crud:make-from-description', '通过描述文件生成 CRUD 控制器和页面', function ()
{/*{{{*/

    $entity_name = command_paramater('entity_name');

    $description = _get_value_from_description_file($entity_name);

    $structs = array_get($description, 'structs', []);
    $entity_display_name = array_get($description, 'display_name', '');
    $entity_description = array_get($description, 'description', '');

    $entity_structs = [];
    foreach ($structs as $column => $struct) {

        $tmp = [
            'name' => $column,
            'datatype' => $struct['type'],
            'display_name' => $struct['display_name'],
            'description' => $struct['description'],
            'format' => array_get($struct, 'format', null),
            'format_description' => array_get($struct, 'format_description', null),
            'allow_null' => array_get($struct, 'allow_null', false),
        ];

        if (array_key_exists('default', $struct)) {
            $tmp['default'] = $struct['default'];
        }

        $entity_structs[] = $tmp;
    }

    $relationships = array_get($description, 'relationships', []);

    $entity_relationships = [];
    foreach ($relationships as $relation_name => $relationship) {

        $relation_entity_name = $relationship['entity'];
        $relation_type = $relationship['type'];

        $entity_relationships[] = [
            'type' => $relation_type,
            'relate_to' => $relation_entity_name,
            'relation_name' => $relation_name,
        ];

        if ($relation_type !== 'has_many') {

            _get_value_from_description_file($relation_entity_name);
        }
    }

    $snaps = array_get($description, 'snaps', []);

    foreach ($snaps as $snap_relation_to_with_dot => $snap) {

        $parent_description = $description;

        $snap_relation_name = '';

        foreach (explode('.', $snap_relation_to_with_dot) as $snap_relation_to) {

            $snap_relation = array_get($parent_description, "relationships.".$snap_relation_to, false);

            otherwise($snap_relation, "与冗余的 $snap_relation_to 没有关联关系");
            otherwise($snap_relation['type'] !== 'has_many', "冗余的 $snap_relation_to 为 has_many 关系，无法冗余字段");

            $parent_description = _get_value_from_description_file($snap_relation['entity']);
            $snap_relation_name = $snap_relation_to;
        }

        $snap_relation_to_structs = $parent_description['structs'];

        foreach ($snap['structs'] as $column) {

            otherwise(array_key_exists($column, $snap_relation_to_structs), "需要冗余的字段 $column 在 $snap_relation_to_with_dot 中不存在");

            $struct = $snap_relation_to_structs[$column];

            $tmp = [
                'name' => 'snap_'.$snap_relation_name.'_'.$column,
                'datatype' => $struct['type'],
                'display_name' => $struct['display_name'],
                'description' => $struct['description'],
                'format' => array_get($struct, 'format', null),
                'format_description' => array_get($struct, 'format_description', null),
                'allow_null' => array_get($struct, 'allow_null', false),
            ];

            if (array_key_exists('default', $struct)) {
                $tmp['default'] = $struct['default'];
            }

            $entity_structs[] = $tmp;
        }
    }

    $dir_name = VIEW_DIR.'/'.$entity_name;

    otherwise(
        is_dir($dir_name)
        || mkdir($dir_name, 0755),
        "当前用户没有权限创建目录 $dir_name");

    $controller_file_string = _generate_controller_file($entity_name, $entity_structs, $entity_relationships);
    $view_add_file_string = _generate_view_add_file($entity_name);
    $view_update_file_string = _generate_view_update_file($entity_name);
    $view_list_file_string = _generate_view_list_file($entity_name);


    // 写文件
    error_log($controller_file_string, 3, $controller_file = CONTROLLER_DIR.'/'.$entity_name.'.php');
    echo $controller_file."\n";
    error_log($view_add_file_string, 3, $file = $dir_name.'/add.php');
    echo $file."\n";
    error_log($view_update_file_string, 3, $file = $dir_name.'/update.php');
    echo $file."\n";
    error_log($view_list_file_string, 3, $file = $dir_name.'/list.php');
    echo $file."\n";
    echo "\n将 $controller_file 加入到 public/index.php 即可响应请求\n";
});/*}}}*/
