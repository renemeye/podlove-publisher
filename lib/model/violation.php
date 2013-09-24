<?php
namespace Podlove\Model;

/**
 * Podlove Constraint Violation
 */
class Violation extends Base {

	public function resolve() {
		$now = date("Y-m-d H:i:s");
		$this->last_checked_at = $now;
		$this->resolved_at = $now;
		$this->save();
	}

	/**
	 * Returns associated constraint instance.
	 * 
	 * @return Podlove\Model\Constraint
	 */
	public function getConstraint() 
	{
		$constraintClassName = str_replace("_", "\\", $this->constraint_class);

		$resource = NULL;
		// FIXME works for \Podlove\Model models only. Should at least fall back to
		// something more generic.
		if ($this->hasResource()) {
			if (stripos($this->resource_class, "Podlove_Model") !== false) {
				$model_name = str_replace("_", "\\", $this->resource_class);
				$resource = $model_name::find_by_id($this->resource_id);
			}
		}

		return new $constraintClassName($resource);
	}

	private function hasResource() {
		return $this->resource_class && $this->resource_id;
	}

}

Violation::property( 'id', 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY' );
Violation::property( 'constraint_class', 'VARCHAR(255)' ); // TODO INDEX
Violation::property( 'resource_class', 'VARCHAR(255)' );
Violation::property( 'resource_id', 'INT' ); // TODO INDEX
Violation::property( 'severity', 'VARCHAR(255)' );
Violation::property( 'context', 'TEXT' );
Violation::property( 'occured_at', 'DATETIME' );
Violation::property( 'last_checked_at', 'DATETIME' );
Violation::property( 'resolved_at', 'DATETIME' );
