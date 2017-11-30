<?php 
namespace Envoy\AddProducts\Controller\Index;

use Magento\Framework\Controller\ResultFactory; 
error_reporting(E_ALL);
ini_set('display_errors', 'on');

class Index extends \Magento\Framework\App\Action\Action {
    
    protected $resultPageFactory;
    protected $_order;
    protected $_cart;
    protected $_product;
    protected $_formKey;
    protected $_messageManager;
    /**
     * Constructor
     * 
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context,
    \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->resultPageFactory = $resultPageFactory;
        //Creating an ObjectManager
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_messageManager = $objectManager->get('Magento\Framework\Message\ManagerInterface');
        $this->_product =  $objectManager->get('Magento\Catalog\Model\Product');
        $this->_cart = $objectManager->get('Magento\Checkout\Model\Cart');
        $this->_formKey = $objectManager->get('Magento\Framework\Data\Form\FormKey');         
        parent::__construct($context);
    }

    /**
     * Execute view action -- On adding the page is redirected to Shopping cart page  
     *
     */
    public function execute()
    {
        $product_id = $this->getRequest()->getParam('id');
        
        //Validation of Requested URL
        /*$url=$this->getRequest()->getRequestUri();
        filter_var($url, FILTER_VALIDATE_URL);   */

        //For multiple products ---> product ids are concatinated by -
        if (strstr($product_id, "-")) { 
            $products = explode("-",$product_id);

            foreach ($products as $product_id) {
                //get product object
                $product = $this->_product->load($product_id);

                //if product exists
                if (is_object($product) && $product->getId()) {
                    try {
                        //Adding product with 1 quantity to the cart
                        $this->_cart->addProduct($product, array('qty' => 1));
                        $this->_cart->save();
                    } catch (\InvalidArgumentException $e) {
                        $this->_messageManager->addSuccess("Error adding to cart.");
                    }
                } else {
                    $this->_messageManager->addError("Product does not exist.");
                }
            }
            $this->_messageManager->addSuccess("Products added to cart.");
            //redirecting to Shopping cart 
            $this->_redirect('checkout/cart');
        }
        else {//if product exists -- Adding single product
            
            $product = $this->_product->load($product_id);
        
            if (is_object($product) && $product->getId()) {
                try {
                    //Adding product with 1 quantity to the cart
                    $this->_cart->addProduct($product, array('qty' => 1));
                    $this->_cart->save();
                } catch (\InvalidArgumentException $e) {
                    $this->_messageManager->addSuccess("Error adding to your cart.");
                }
                $this->_messageManager->addSuccess("Product added to your cart.");
            } 
            else {
                $this->_messageManager->addError("Product does not exist.");
            }    
            //redirecting to Shopping cart 
            $this->_redirect('checkout/cart');
        }
    }
}