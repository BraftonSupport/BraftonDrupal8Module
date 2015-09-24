<?php

class article_loader extends feed_loader{
    
    public function __construct(){
        parent::__construct();
        //used for
        
    }
    //may be moved to feed_loader if we can get the method get passed the same info
    public function assign_category(){
        
    }
    
    public function import_articles(){
        foreach obj as obj2
            $this->import_single_article(obj2);
        
    }
    /**
     * @obj $article single article object
     */
    public function import_single_article($article){
        //used to do the magic on a single article object
        
    }
}