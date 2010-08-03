<?php
/*
 *  The MIT License
 *
 *  Copyright (c) 2010 Johannes Mueller <circus2(at)web.de>
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

abstract class MwbExporter_Core_Model_Index
{
    protected $data = null;
    protected $attributes = null;
    
    protected $id = null;
    protected $referencedColumn = array();
    
    public function __construct($data)
    {
        $this->attributes = $data->attributes();
        $this->data       = $data;
        $this->id         = (string) $this->attributes['id'];
        
        /**
         * iterate on column configuration
         */
        foreach($this->data->value as $key => $node){
            $attributes         = $node->attributes();         // read attributes
            $key                = (string) $attributes['key']; // assign key
            $this->config[$key] = (string) $node[0];           // assign value
        }
        
        /**
         * iterate on links to other wb objects
         */
        foreach($this->data->link as $key => $node){
            $attributes         = $node->attributes();
            $key                = (string) $attributes['key'];
        }

        foreach($this->data->xpath("value[@key='columns']/value") as $indexColumn){
            foreach($indexColumn->link as $node){
                if( (string) $node['key'] === 'referencedColumn' ){
                    // for primary indexes ignore external index
                    // definition and set column to primary instead
                    if($this->config['name'] == 'PRIMARY'){
                        MwbExporter_Core_Registry::get((string) $node)->markAsPrimary();
                        return;
                    }
                    $this->referencedColumn[] = MwbExporter_Core_Registry::get((string) $node);
                }
            }
        }
        
        MwbExporter_Core_Registry::get((string)$this->data->link)->injectIndex($this);
    }

}