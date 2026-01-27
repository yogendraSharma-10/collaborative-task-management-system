import React, { useState, useEffect } from 'react';
import TaskCard from './components/TaskCard';
import * as taskService from './api/taskService'; // Import all functions from taskService
import './styles/main.css'; // Main application styles

/**
 * The main application component for the Collaborative Task Management System.
 * Manages global state for tasks, filters, and interactions with the task API.
 */
function App() {
  // State for managing the list of tasks fetched from the API
  const [tasks, setTasks] = useState([]);
  // State to indicate if data is currently being loaded
  const [loading, setLoading] = useState(true);
  // State to store any error messages during API operations
  const [error, setError] = useState(null);

  // State for the new task creation form inputs
  const [newTask, setNewTask] = useState({
    title: '',
    description: '',
    assignedTo: '',
    projectId: '', // Tasks are associated with projects
    dueDate: '',
    status: 'pending' // Default status for new tasks
  });

  // State for filtering tasks by status ('all', 'pending', 'in-progress', 'completed')
  const [filterStatus, setFilterStatus] = useState('all');
  // State for filtering tasks by project ('all' or a specific project ID)
  const [filterProject, setFilterProject] = useState('all');

  // Mock projects for demonstration purposes. In a real application,
  // these would typically be fetched from a separate project management API.
  const [projects, setProjects] = useState([
    { id: 'proj1', name: 'Website Redesign' },
    { id: 'proj2', name: 'Marketing Campaign Launch' },
    { id: 'proj3', name: 'Backend API Development' },
    { id: 'proj4', name: 'Mobile App Integration' },
  ]);

  /**
   * Fetches tasks from the backend API, applying current filters.
   * Handles loading and error states during the fetch operation.
   */
  const fetchTasks = async () => {
    setLoading(true);
    setError(null); // Clear previous errors
    try {
      const fetchedTasks = await taskService.getAllTasks();
      let filtered = fetchedTasks;

      // Apply status filter
      if (filterStatus !== 'all') {
        filtered = filtered.filter(task => task.status === filterStatus);
      }
      // Apply project filter
      if (filterProject !== 'all') {
        filtered = filtered.filter(task => task.projectId === filterProject);
      }
      setTasks(filtered);
    } catch (err) {
      console.error('Failed to fetch