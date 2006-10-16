package se.repos.issu.persistence;

import java.util.Collection;
import java.util.Iterator;

import org.hibernate.SessionFactory;

import se.repos.issu.domain.Issue;

public class IssueDaoImpl implements IssueDao {

	private SessionFactory sessionFactory;
	
	/* (non-Javadoc)
	 * @see se.repos.issu.persistence.IssueDao#getAll()
	 */
	@SuppressWarnings("unchecked")
	public Collection<Issue> getAll() {
		Iterator<Issue> it = sessionFactory.getCurrentSession()
		.createQuery("from se.repos.issu.domain.Issue").iterate(); 
		while (it.hasNext()) {
			it.next().toString();
		}
		return sessionFactory.getCurrentSession()
			.createQuery("from se.repos.issu.domain.Issue").list();
	}

	public void setSessionFactory(SessionFactory sessionFactory) {
		this.sessionFactory = sessionFactory;
	}

	public void create(Issue issue) {

		
		sessionFactory.getCurrentSession()
			.save(issue);
	}
	
	
}
