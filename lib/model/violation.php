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
	public function getConstraint() {
		$className = str_replace("_", "\\", $this->constraint_class);

		// FIXME: make this work without scope
		$resource = NULL;
		if ($this->scope == 'episode' && $this->scope_resource_id) {
			$resource = Episode::find_by_id($this->scope_resource_id);
		}

		return new $className($resource);
	}

}

Violation::property( 'id', 'INT NOT NULL AUTO_INCREMENT PRIMARY KEY' );
Violation::property( 'constraint_class', 'VARCHAR(255)' ); // TODO INDEX
Violation::property( 'scope_resource_id', 'INT' ); // TODO INDEX
Violation::property( 'scope', 'VARCHAR(255)' );
Violation::property( 'severity', 'VARCHAR(255)' );
Violation::property( 'context', 'TEXT' );
Violation::property( 'occured_at', 'DATETIME' );
Violation::property( 'last_checked_at', 'DATETIME' );
Violation::property( 'resolved_at', 'DATETIME' );
