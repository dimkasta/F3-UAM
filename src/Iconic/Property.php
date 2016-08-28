<?php
/**
 * Created by PhpStorm.
 * User: dimkasta
 * Date: 28/08/16
 * Time: 02:09
 */

namespace Iconic;


abstract class Property
{
    use Log;

    protected $dirty;
    protected $value;
    protected $required;
    protected $issues;

    abstract public function sanitize();

    abstract public function isPropertyValid();

    public function __construct($value, $required)
    {
        $this->enableLog(true);
        $this->log("Constructing");

        $this->dirty = $value;
        $this->required = $required;
        $this->issues = [];
        $this->setValue();
    }

    public function setValue() {
        $this->log("Setting value");
        $this->isRequirementOK();
        $this->isPropertyValid();
        if($this->isValid()) {
            $this->log("Valid");
            $this->sanitize();
            $this->value = $this->dirty;
        }
        else {
            $this->log("Not valid");
            $this->value = "";
        }
    }

    public function getValue() {
        $this->log("Getting");
        $this->sanitize();
        $this->escape();
        return $this->value;
    }

    public function isRequirementOK() {
        $this->log("Is required ok?");
        if($this->required && empty($this->dirty)) {
            array_push($this->issues, "empty");
        }
    }

    public function isValid() {
        $this->log("Is Valid?");
        return count($this->issues) == 0;
    }

    public function escape()
    {
        $this->log("Escaping");
        $untagged = strip_tags($this->value);
        $escaped = htmlspecialchars($untagged);
        $this->dirty = $escaped;
    }

    public function __toString() {
        $this->log("Tostringing");
        return $this->getValue();
    }
}