/* $license_header$
 */
package se.repos.issu.services;

import se.repos.issu.domain.Issue;

public interface IssueService {

	/**
	 * Store a new issue 
	 * @param data Populated instance without ID
	 */
	public abstract void create(Issue data);

	/**
	 * Open existing issue with known key
	 * @param id issue number
	 * @return that issue
	 */
	public abstract Issue open(long id);
	
}
