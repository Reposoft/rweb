/* $license_header$
 */
package se.repos.issu.services;

import org.hibernate.SessionFactory;

import se.repos.issu.domain.Issue;

public class IssueServiceHibernate implements IssueService {

	SessionFactory sessionFactory;
	
	public void setSessionFactory(SessionFactory sessionFactory) {
		this.sessionFactory = sessionFactory;
	}

	public void create(Issue data) {
		sessionFactory.getCurrentSession().save(data);
	}

	public Issue open(long id) {
		return (Issue) sessionFactory.getCurrentSession().get(Issue.class, id);
	}

}
