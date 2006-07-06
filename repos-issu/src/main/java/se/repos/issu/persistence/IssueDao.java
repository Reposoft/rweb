package se.repos.issu.persistence;

import java.util.Collection;

import se.repos.issu.domain.Issue;

public interface IssueDao {

	public abstract Collection<Issue> getAll();
	
	public abstract void create(Issue issue);

}