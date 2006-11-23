/* $license_header$
 */
package se.repos.svn.checkout;

import se.repos.svn.VersionedProperty;

public interface VersionedFileProperties extends VersionedProperties {

	/**
	 * @return true if the file has a specified mime type
	 */
	boolean hasMimeType();
	
	/**
	 * Gets the current value of the mime type property (not a value derived from filetype)
	 * @return The mime type of the file, if specified
	 */
	VersionedProperty getMimeType();
	
	/**
	 * Allows Subversion to override the default mime type derived from file type.
	 * @param property New value of the mime type property, or null to remove mime type setting.
	 *  Removing mime type is a rare operation, so there is no dedicated method for it.
	 */
	void setMimeType(MimeType property);
	
	/**
	 * Provides validation of mime type before setting property
	 */
	public static class MimeType implements VersionedProperty {
		public static final String PROPERTY_NAME = "svn:mime-type";
		private String value;
		public MimeType(String value) {
			if (value==null) throw new IllegalArgumentException("Mime Type value can not be null.");
			if (value.length()==0) throw new IllegalArgumentException("Empty Mime Type not allowed.");
			if (value.length()<3) throw new IllegalArgumentException("Mime types can not be shorter than three characters");
			if (value.indexOf('/')<1) throw new IllegalArgumentException("Mime type must contain a slash");
			this.value = value;
		}
		public String getName() {
			return PROPERTY_NAME;
		}
		public String getValue() {
			return value;
		}
	}
	
}
