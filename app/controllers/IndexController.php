<?php

// use App\Forms\ItemsForm;
// use App\Models\Items;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;

class IndexController extends ControllerBase
{

    /**
     * Index action
     */
    public function indexAction()
    {
        $this->persistent->parameters = null;
    }
    
    /**
     * Searches for items
     */
    public function searchAction()
    {
        $numberPage = 1;
        if ($this->request->isPost()) {
            $query = Criteria::fromInput($this->di, 'Items', $_POST);
            $this->persistent->parameters = $query->getParams();
        } else {
            $numberPage = $this->request->getQuery("page", "int");
        }
        
        $parameters = $this->persistent->parameters;
        if (!is_array($parameters)) {
            $parameters = [];
        }
        $parameters["order"] = "id";
        
        $items = Items::find($parameters);
        if (count($items) == 0) {
            $this->flash->notice("The search did not find any items");
            
            $this->dispatcher->forward([
                "controller" => "index",
                "action" => "index"
            ]);
            
            return;
        }
        
        $paginator = new Paginator([
            'data' => $items,
            'limit'=> 10,
            'page' => $numberPage
        ]);
        
        $this->view->page = $paginator->getPaginate();
    }
    
    /**
     * Displays the creation form
     */
    public function newAction()
    {
        
    }
    
    /**
     * Edits a item
     *
     * @param string $id
     */
    public function editAction($id)
    {
        if (!$this->request->isPost()) {
            
            $item = Items::findFirstByid($id);
            if (!$item) {
                $this->flash->error("item was not found");
                
                $this->dispatcher->forward([
                    'controller' => "index",
                    'action' => 'index'
                ]);
                
                return;
            }
            
            $this->view->id = $item->id;
            
            $this->tag->setDefault("id", $item->id);
            $this->tag->setDefault("title", $item->title);
            $this->tag->setDefault("description", $item->description);
            $this->tag->setDefault("price", $item->price);
            $this->tag->setDefault("image", $item->image);
            
        }
    }
    
    /**
     * Creates a new item
     */
    public function createAction()
    {
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "index",
                'action' => 'index'
            ]);
            
            return;
        }
        
        $item = new Items();
        $item->Title = $this->request->getPost("title");
        $item->Description = $this->request->getPost("description");
        $item->Price = $this->request->getPost("price");
        $item->Image = $this->request->getPost("image");
        
        
        if (!$item->save()) {
            foreach ($item->getMessages() as $message) {
                $this->flash->error($message);
            }
            
            $this->dispatcher->forward([
                'controller' => "index",
                'action' => 'new'
            ]);
            
            return;
        }
        
        $this->flash->success("item was created successfully");
        
        $this->dispatcher->forward([
            'controller' => "index",
            'action' => 'index'
        ]);
    }
    
    /**
     * Saves a item edited
     *
     */
    public function saveAction()
    {
        
        if (!$this->request->isPost()) {
            $this->dispatcher->forward([
                'controller' => "index",
                'action' => 'index'
            ]);
            
            return;
        }
        
        $id = $this->request->getPost("id");
        $item = Items::findFirstByid($id);
        
        if (!$item) {
            $this->flash->error("item does not exist " . $id);
            
            $this->dispatcher->forward([
                'controller' => "index",
                'action' => 'index'
            ]);
            
            return;
        }
        
        $item->Title = $this->request->getPost("title");
        $item->Description = $this->request->getPost("description");
        $item->Price = $this->request->getPost("price");
        $item->Image = $this->request->getPost("image");
        
        
        if (!$item->save()) {
            
            foreach ($item->getMessages() as $message) {
                $this->flash->error($message);
            }
            
            $this->dispatcher->forward([
                'controller' => "index",
                'action' => 'edit',
                'params' => [$item->id]
            ]);
            
            return;
        }
        
        $this->flash->success("item was updated successfully");
        
        $this->dispatcher->forward([
            'controller' => "index",
            'action' => 'index'
        ]);
    }
    
    /**
     * Deletes a item
     *
     * @param string $id
     */
    public function deleteAction($id)
    {
        $item = Items::findFirstByid($id);
        if (!$item) {
            $this->flash->error("item was not found");
            
            $this->dispatcher->forward([
                'controller' => "index",
                'action' => 'index'
            ]);
            
            return;
        }
        
        if (!$item->delete()) {
            
            foreach ($item->getMessages() as $message) {
                $this->flash->error($message);
            }
            
            $this->dispatcher->forward([
                'controller' => "index",
                'action' => 'search'
            ]);
            
            return;
        }
        
        $this->flash->success("item was deleted successfully");
        
        $this->dispatcher->forward([
            'controller' => "index",
            'action' => "index"
        ]);
    }

}

