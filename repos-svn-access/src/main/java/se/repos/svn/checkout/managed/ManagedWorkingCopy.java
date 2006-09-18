/* $license_header$
 */
package se.repos.svn.checkout.managed;

import java.io.File;

import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ConflictInformation;
import se.repos.svn.checkout.NotifyListener;
import se.repos.svn.checkout.ReposWorkingCopy;

/**
 * A subversion client like all the others, with no user logic
 * 
 * A more classic versioning client than {@link ReposWorkingCopy}, with fine grained explicit operations.
 *
 * Still uses {@link ReposWorkingCopy#synchronize()} in place of commit.
 * 
 * @author Staffan Olsson (solsson)
 * @version $Id$
 */
public class ManagedWorkingCopy implements ReposWorkingCopy {

	public ManagedWorkingCopy(CheckoutSettings settings) {
		
	}

	public void add(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#add not implemented yet");
		}
		
	}

	public void addAll() {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#addAll not implemented yet");
		}
		
	}

	public void addNotifyListener(NotifyListener notifyListener) {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#addNotifyListener not implemented yet");
		}
		
	}

	public void checkout() {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#checkout not implemented yet");
		}
		
	}

	public void commit(String commitMessage) throws ConflictException {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#commit not implemented yet");
		}
		
	}

	public void delete(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#delete not implemented yet");
		}
		
	}

	public boolean hasLocalChanges(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#hasLocalChanges not implemented yet");
		}
		return false;
	}

	public boolean isVersioned(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#isVersioned not implemented yet");
		}
		return false;
	}

	public void lock(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#lock not implemented yet");
		}
		
	}

	public void move(File from, File to) {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#move not implemented yet");
		}
		
	}

	public void revert(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#revert not implemented yet");
		}
		
	}

	public void update(File path) {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#update not implemented yet");
		}
		
	}

	public boolean hasLocalChanges() {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#hasLocalChanges not implemented yet");
		}
		return false;
	}

	public void markConflictResolved(ConflictInformation conflictInformation) {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#markConflictResolved not implemented yet");
		}
		
	}

	public void synchronize(String commitMessage) throws ConflictException {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#synchronize not implemented yet");
		}
		
	}

	public void update() throws ConflictException {
		if (true) {
			throw new UnsupportedOperationException("Method ManagedWorkingCopy#update not implemented yet");
		}
		
	}

}
