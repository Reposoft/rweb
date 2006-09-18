package se.repos.svn.checkout.simple;

import org.slf4j.Logger;
import org.slf4j.LoggerFactory;

import se.repos.svn.checkout.CheckoutSettings;
import se.repos.svn.checkout.ConflictException;
import se.repos.svn.checkout.ConflictInformation;
import se.repos.svn.checkout.MandatoryReposOperations;
import se.repos.svn.checkout.ReposWorkingCopy;
import se.repos.svn.checkout.ReposWorkingCopyFactory;
import se.repos.svn.checkout.client.CheckoutSettingsValidator;
import se.repos.validation.Validation;
import se.repos.validation.ValidationRule;

/**
 * Subversion client for an office user working in a windows folder.
 * 
 * This is currently a proof-of-concept. It is likely that failsafe operation
 * of a working copy is not possible without constant monitoring of the folder.
 *
 * Designed to make interactions minimal and as clear sa possible for a
 * user who has no experience with source code versioning systems.
 * 
 * Features above the normal operations:
 * - Automatic add at synchronize.
 * TODO Automatic delete at synchronize.
 * TODO Automatic locks?
 * TODO Handle situations where user has moved a working copy folder (with no svn move)
 *
 * @author Staffan Olsson
 * @since 2006-apr-16
 * @version $Id$
 * @todo Should all files be write-protected until locked?
 * @todo handle when files and directories have been moved in windows folder
 * @todo handle commit message (input) and commit errors (result)
 * @todo rename to SimpleWorkingCopy
 */
public class PersonalWorkingCopy implements MandatoryReposOperations {

	final Logger logger = LoggerFactory.getLogger(this.getClass());
  
    private static final ValidationRule<CheckoutSettings> CHECKOUT_SETTINGS_VALIDATOR = 
    	Validation.rule(CheckoutSettingsValidator.class);
    
    private ReposWorkingCopy workingCopy;
    
    /**
     * Open up an existing working copy.
     * If the folder is empty nothing can be done until {@link #checkout()} is called.
     */
    public PersonalWorkingCopy(CheckoutSettings settings) {
        super();
        logger.info("Initializing working copy with settings: {}", settings);
        CHECKOUT_SETTINGS_VALIDATOR.validate(settings);
        this.workingCopy = ReposWorkingCopyFactory.getClient(settings);
        
        if (settings.getWorkingCopyDirectory().list().length == 0) {
        	logger.debug("Working copy direcotry {} is empty, need new checkout", settings.getWorkingCopyDirectory().getAbsolutePath());
        	workingCopy.checkout();
        }
    }

    public boolean hasLocalChanges() {
    	return workingCopy.hasLocalChanges();
    }
    
    public void synchronize(String commitMessage) throws ConflictException {
        // update
        logger.debug("Starting synchronize() with an update");
        this.update();
        // release all locks
        //TODO locks
        // add all unadded
        logger.debug("Adding all unversioned files");
        workingCopy.addAll();
        // commit
        if (hasLocalChanges()) {
        	logger.info("There is local changes, performing commit.");
            workingCopy.commit(commitMessage);
        } else {
        	logger.info("There are no local changes");
        }
    }
    
	public void update() throws ConflictException {
		workingCopy.update();
	}

	public void markConflictResolved(ConflictInformation conflictInformation) {
		if (true) {
			throw new UnsupportedOperationException("Method PersonalWorkingCopy#markConflictResolved not implemented yet");
		}
	}

}
