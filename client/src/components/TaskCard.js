import React from 'react';
import PropTypes from 'prop-types';
import '../styles/main.css'; // Import global styles for consistent look and feel

/**
 * TaskCard Component
 *
 * Displays an individual task with its details, status, priority, assignee, and due date.
 * Provides actions to edit, delete, and change the status of the task.
 *
 * @param {object} props - The component props.
 * @param {object} props.task - The task object to display.
 * @param {function} props.onEdit - Callback function to handle task edit action.
 * @param {function} props.onDelete - Callback function to handle task delete action.
 * @param {function} props.onStatusChange - Callback function to handle task status change.
 */
const TaskCard = ({ task, onEdit, onDelete, onStatusChange }) => {
  // Destructure task properties for easier access
  const { id, title, description, status, priority, dueDate, assignee } = task;

  /**
   * Handles the change event for the status dropdown.
   * Calls the `onStatusChange` prop with the task ID and new status value.
   * @param {object} e - The event object from the select element.
   */
  const handleStatusChange = (e) => {
    onStatusChange(id, e.target.value);
  };

  /**
   * Returns a CSS class name based on the task's current status.
   * This allows for status-specific styling (e.g., different background colors).
   * @param {string} currentStatus - The current status of the task.
   * @returns {string} The CSS class name for the status.
   */
  const getStatusClassName = (currentStatus) => {
    switch (currentStatus) {
      case 'To Do':
        return 'status-todo';
      case 'In Progress':
        return 'status-in-progress';
      case 'Done':
        return 'status-done';
      case 'Blocked':
        return 'status-blocked';
      default:
        return '';
    }
  };

  return (
    <div className="task-card" data-task-id={id}>
      <div className="task-card-header">
        <h3 className="task-card-title">{title}</h3>
        <div className={`task-card-status ${getStatusClassName(status)}`}>
          <label htmlFor={`status-select-${id}`} className="sr-only">Change status for task "{title}"</label>
          <select
            id={`status-select-${id}`}
            value={status}
            onChange={handleStatusChange}
            className="task-status-select"
            aria-label={`Change status for task "${title}"`}
          >
            <option value="To Do">To Do</option>
            <option value="In Progress">In Progress</option>
            <option value="Done">Done</option>
            <option value="Blocked">Blocked</option>
          </select>
        </div>
      </div>

      {description && <p className="task-card-description">{description}</p>}

      <div className="task-card-details">
        {assignee && (
          <span className="task-card-assignee">
            Assigned to: <strong>{assignee.name}</strong>
          </span>
        )}
        {dueDate && (
          <span className="task-card-due-date">
            Due: <strong>{new Date(dueDate).toLocaleDateString()}</strong>
          </span>
        )}
        <span className={`task-card-priority priority-${priority.toLowerCase().replace(' ', '-')}`}>
          Priority: <strong>{priority}</strong>
        </span>
      </div>

      <div className="task-card-actions">
        <button
          onClick={() => onEdit(id)}
          className="btn btn-secondary"
          aria-label={`Edit task "${title}"`}
        >
          Edit
        </button>
        <button
          onClick={() => onDelete(id)}
          className="btn btn-danger"
          aria-label={`Delete task "${title}"`}
        >
          Delete
        </button>
      </div>
    </div>
  );
};

// Define PropTypes for type checking and documentation
TaskCard.propTypes = {
  task: PropTypes.shape({
    id: PropTypes.string.isRequired,
    title: PropTypes.string.isRequired,
    description: PropTypes.string,
    status: PropTypes.oneOf(['To Do', 'In Progress', 'Done', 'Blocked']).isRequired,
    priority: PropTypes.oneOf(['Low', 'Medium', 'High']).isRequired,
    dueDate: PropTypes.string, // ISO date string
    assignee: PropTypes.shape({
      id: PropTypes.string.isRequired,
      name: PropTypes.string.isRequired,
    }),
    projectId: PropTypes.string.isRequired, // Essential for project organization
    createdAt: PropTypes.string.isRequired,
    updatedAt: PropTypes.string.isRequired,
  }).isRequired,
  onEdit: PropTypes.func.isRequired,
  onDelete: PropTypes.func.isRequired,
  onStatusChange: PropTypes.func.isRequired,
};

export default TaskCard;