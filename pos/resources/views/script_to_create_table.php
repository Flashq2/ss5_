$get_field = DB::getSchemaBuilder()->getColumnListing('table_record');
        foreach ($field as $f){
            $i = 0;
            $data = new TableModel();
            $data->table_name = 'customers';
            $data->user = Auth::user()->email;
            $data->type = 'input';
            $data->field_name = $f;
            $data->show = 'on';
            $data->not_null = 'no';
            $data->is_number = 'no';
            $data->order_field = $i++;
            $data->description = str_replace('-',' ',$f);
            $data->save();  
        }