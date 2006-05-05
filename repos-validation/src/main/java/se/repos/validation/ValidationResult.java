package se.repos.validation;

public enum ValidationResult {
	VALID (true), 
	INVALID (false);
	
	private final boolean valueAccepted;
	
	ValidationResult(boolean valueAccepted) {
		this.valueAccepted = valueAccepted;
	}
	
	public boolean passed() {
		return valueAccepted;
	}
	
	public boolean failed() {
		return !valueAccepted;
	}
}
