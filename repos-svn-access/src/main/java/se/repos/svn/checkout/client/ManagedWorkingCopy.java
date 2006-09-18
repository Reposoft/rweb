/* $license_header$
 */
package se.repos.svn.checkout.client;

import java.io.File;

import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.simple.PersonalWorkingCopy;

public class ManagedWorkingCopy extends PersonalWorkingCopy implements
		ReposWorkingCopyClient {

	public ManagedWorkingCopy(CheckoutSettings settings) {
		super(settings);
		// TODO auto-generated
	}

	public void add(File path) {
		if (true) {
			throw new UnsupportedOperationException(
					"Method ManagedWorkingCopy#add not implemented yet");
		}

	}

	public void delete(File path) {
		if (true) {
			throw new UnsupportedOperationException(
					"Method ManagedWorkingCopy#delete not implemented yet");
		}

	}

	public void move(File from, File to) {
		if (true) {
			throw new UnsupportedOperationException(
					"Method ManagedWorkingCopy#move not implemented yet");
		}

	}

	public void update(File path) {
		if (true) {
			throw new UnsupportedOperationException(
					"Method ManagedWorkingCopy#update not implemented yet");
		}

	}

}
