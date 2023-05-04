<?php


namespace Cashew\Kernel\Error;

class ErrorPool {

    /**
     * @var Error[]
     */
    protected array $errors = [];

    /**
     * @return bool
     */
    public function hasErrors(): bool {
        if (!empty($this->getErrors())) {
            return true;
        }

        return false;
    }

    /**
     * @return Error[]
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * @param Error[] $errors
     */
    public function setErrors(array $errors): void {
        $this->errors = $errors;
    }

    public function addToJsonPool(): void {
        foreach ($this->getErrors() as $error) {
            $error->addToJsonPool();
        }
    }

    /**
     * @param Error $error
     */
    public function addError(Error $error): void {
        $errors = $this->getErrors();
        $errors[] = $error;

        $this->setErrors($errors);
    }

}
