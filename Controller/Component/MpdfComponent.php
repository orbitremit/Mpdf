<?php

/**
 * Component for working with mPDF class.
 * mPDF has to be in the vendors directory.
 */
class MpdfComponent extends Component
{

    /**
     * Instance of mPDF class
     * @var object
     */
    protected $pdf;

    /**
     * Default values for mPDF constructor
     * @var array
     */
    protected $_configuration = array(
        // mode: 'c' for core fonts only, 'utf8-s' for subset etc.
        'mode' => 'c',
        'format' => 'A4',
        'font_size' => 0,
        'font' => null,
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 16,
        'margin_bottom' => 16,
        'margin_header' => 9,
        'margin_footer' => 9
    );

    /**
     * Flag set to true if mPDF was initialized
     * @var bool
     */
    protected $_init = false;

    /**
     * Name of the file on the output
     * @var string
     */
    protected $_filename = null;

    /**
     * Destination - posible values are I, D, F, S
     * @var string
     */
    protected $_output = 'I';

    /**
     * Initialize
     * Add vendor and define mPDF class.
     */
    public function init($configuration = array())
    {
        // error_reporting(0);
        // Configure::write('debug', '0');

        require_once ROOT . DS . 'vendor' . DS . 'mpdf' . DS . 'mpdf' . DS . 'mpdf.php';

        if (!class_exists('mPDF')) {
            throw new CakeException('Vendor class mPDF not found!');
        }

        $c = array();
        foreach ($this->_configuration as $key => $val) {
            $c[$key] = array_key_exists($key, $configuration) ? $configuration[$key] : $val;
        }
        $this->pdf = new mPDF($c['mode'], $c['format'], $c['font_size'], $c['font'], $c['margin_left'], $c['margin_right'], $c['margin_top'], $c['margin_bottom'], $c['margin_header'], $c['margin_footer']);
        $this->_init = true;
    }

    /**
     * Set filename of the output file
     */
    public function setFilename($filename)
    {
        $this->_filename = (string) $filename;
    }

    /**
     * Set destination of the output
     */
    public function setOutput($output)
    {
        if (in_array($output, array('I', 'D', 'F', 'S'))) {
            $this->_output = $output;
        }
    }

    /**
     * Shutdown of the component
     * View is rendered but not yet sent to browser.
     */
    public function shutdown(Controller $controller)
    {
        if ($this->_init) {
            $this->pdf->WriteHTML((string) $controller->response);
            $this->pdf->Output($this->_filename, $this->_output);
            exit;
        }
    }

    /**
     * Passing method calls and variable setting to mPDF library.
     */
    public function __set($name, $value)
    {
        $this->pdf->$name = $value;
    }

    public function __get($name)
    {
        return $this->pdf->$name;
    }

    public function __isset($name)
    {
        return isset($this->pdf->$name);
    }

    public function __unset($name)
    {
        unset($this->pdf->$name);
    }

    public function __call($name, $arguments)
    {
        call_user_func_array(array($this->pdf, $name), $arguments);
    }

    public function generatePDF($content)
    {
        $this->pdf->WriteHTML((string) $content);
        return $this->pdf->Output("", $this->_output);
    }
}
