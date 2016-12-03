<?php

class ArrayPagination {

    protected $page;

    protected $size;

    protected $total_count;

    protected $total_page;

    protected $limit;

    public function __construct($page = 1, $size = 20)
    {
        $this->page = abs(intval($page)) ?: 1;
        $this->size = abs(intval($size)) ?: 20;
        $this->limit = ($this->page - 1) * $this->size . ', ' . $this->size;
    }

    public function open($array)
    {
        $this->total_count = count($array);
        return array(
            'data'  =>  array_slice($array, ($this->page - 1) * $this->size, $this->size),
            'pager' =>  $this->getPager(),
        );
    }

    public function getPager($pagesize = 10)
    {
        $this->total_page = ceil($this->total_count / $this->size);
        return $this->total_count > $this->size ? [
            'total_count'	=>	$this->total_count,
            'page_size'		=>	$this->size,
            'total_page'	=>	$this->total_page,
            'first_page'	=>	1,
            'prev_page'		=>	((1 == $this->page) ? 1 : ($this->page - 1)),
            'next_page'		=>	(($this->page == $this->total_page ) ? $this->total_page : ($this->page + 1)),
            'last_page'		=>	$this->total_page,
            'current_page'	=>	$this->page,
            'all_pages'		=>	$this->makepage($pagesize),
        ] : [];
    }

    private function makepage($size = 10) {
        $page_start	=	1;
        $half		=	intval($size / 2);
        $page_start	=	max($page_start, $this->page - $half);
        $page_end	=	min($page_start + $size - 1, $this->total_page);
        $page_start	=	max(1, $page_end - $size + 1);
        return range($page_start, $page_end);
    }

}
