import { render, screen, fireEvent } from '@testing-library/react';
import CommentThread from '../CommentThread.jsx';

const comments = [
  { id: 1, author: 'Alice', content: 'Hi', date: '2024-01-01', avatar: '', replies: [] }
];

test('renders comments and opens report dialog', () => {
  const onReport = jest.fn();
  render(<CommentThread comments={comments} onReport={onReport} />);
  expect(screen.getByText('Comments (1)')).toBeInTheDocument();
  fireEvent.click(screen.getByLabelText('Report'));
  expect(screen.getByRole('dialog')).toBeInTheDocument();
});
