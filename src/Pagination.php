<?php
namespace KeriganSolutions\KMARealtor;

class Pagination {

    protected $searchMeta;
    protected $searchParams;
    protected $hideVars;

    public function __construct($searchMeta, $searchParams, $hideVars = false)
    {
        $this->searchMeta = $searchMeta;
        $this->searchParams = $searchParams;
        $this->hideVars = $hideVars;
    }

    public function buildURL()
    {

        $request = '?q=search';
        foreach($this->searchParams as $key => $var){
            if(is_array($var)){
                $request .= '&' . $key . '=';
                foreach($var as $k => $v){
                    $request .= '&' . $key . '[]=' . $v;
                }
            }else{
                if($var != '' && $var != 'Any'){
                    $request .= '&' . $key . '=' . $var;
                }
            }
        }

        return $request;
    }

    public function buildPagination()
    {        
        global $wp;
        
        $pages = $this->searchMeta;
        $request = '/' . $wp->request . (is_array($this->searchParams) ? $this->buildURL() : '?q=browse');
        $currentPage = $pages->current_page;

        $output = '';

        $output .= '<nav aria-label="search results pagination"><ul class="pagination m-0">';
        if(isset($pages->links->previous)){
            $link = $request . '&page=' . ($currentPage - 1);
            $output .= '<li class="page-item"><a class="page-link" href="'. $link .'" aria-label="Previous">
                <span aria-hidden="true">&laquo;</span> Previous
            </a></li>';
        }else{
            $output .= '<li class="page-item disabled"><a class="page-link disabled" tabindex="-1" aria-label="Previous">
                 <span aria-hidden="true">&laquo;</span> Previous
            </a></li>';
        }

        if($pages->total_pages >= 3){
            $startPage = ($currentPage > 2 ? $currentPage - 2 : 1 );
            $endPage   = ($currentPage < $pages->total_pages - 2 ? $currentPage + 2 : $pages->total_pages );

            if($startPage != 1){
                $output .= '<li class="page-item"><a class="page-link" href="'. $request . '&page=1">1</a></li>';
            }
            if($startPage > 3){
                $output   .= '<li class="page-item disabled"><a class="page-link disabled" tabindex="-1" >...</a></li>';
                $startPage = ( $currentPage < $pages->total_pages - 2 ? $currentPage - 1 : $startPage);
                $endPage   = ( $currentPage < $pages->total_pages - 3 ? $currentPage + 1 : $endPage);
            }

            for($i = $startPage; $i <= $endPage; $i++){
                $link = $request . '&page=' . $i;
                $output .= '<li class="page-item' . ($currentPage == $i ? ' active' : '') . '"><a class="page-link" href="'. $link .'">' . $i .'</a></li>';
            }

            if($endPage < $pages->total_pages - 1){
                $output .= '<li class="page-item disabled"><a class="page-link disabled" tabindex="-1" >...</a></li>';
            }
            if($endPage < $pages->total_pages){
                $output .= '<li class="page-item"><a class="page-link" href="'. $request . '&page=' . $pages->total_pages .'">'. $pages->total_pages .'</a></li>';
            }
        }

        if(isset($pages->links->next)){
            $link = $request . '&page=' . ($currentPage + 1);
            $output .= '<li class="page-item"><a class="page-link" href="'. $link .'" aria-label="Next">
                Next <span aria-hidden="true">&raquo;</span>
            </a></li>';
        }else{
            $output .= '<li class="page-item disabled"><a class="page-link disabled" tabindex="-1" aria-label="Next">
                Next <span aria-hidden="true">&raquo;</span>
            </a></li>';
        }
        
        $output .= '</ul></nav>';

        return $output;
    }
}