/*******************************************************************

Part of the Fritzing project - http://fritzing.org
Copyright (c) 2007-2011 Fachhochschule Potsdam - http://fh-potsdam.de

Fritzing is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Fritzing is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Fritzing.  If not, see <http://www.gnu.org/licenses/>.

********************************************************************

$Revision: 6708 $:
$Author: irascibl@gmail.com $:
$Date: 2012-12-13 23:52:56 +0100 (Thu, 13 Dec 2012) $

********************************************************************/

#ifndef AUTOROUTEPROGRESSDIALOG_H
#define AUTOROUTEPROGRESSDIALOG_H

#include <QDialog>
#include <QProgressBar>
#include <QLabel>
#include <QSpinBox>
#include <QDialogButtonBox>

class AutorouteProgressDialog : public QDialog
{
Q_OBJECT

public:
	AutorouteProgressDialog(const QString & title, bool zoomAndPan, bool stopButton, bool bestButton, bool spin, class ZoomableGraphicsView * view, QWidget *parent = 0);
	~AutorouteProgressDialog();

protected:
	void closeEvent(QCloseEvent *);

public slots:
	void setMinimum(int);
	void setMaximum(int);
	void setValue(int);
	void sendSkip();
	void sendCancel();
	void sendStop();
	void setSpinLabel(const QString &);
	void setMessage(const QString &);
	void setSpinValue(int);
    void disableButtons();
    void sendBest();

signals:
	void skip();
	void cancel();
	void stop();
    void best();
	void spinChange(int);

protected slots:
	void internalSpinChange(int);

protected:
	QProgressBar * m_progressBar;	
	QLabel * m_spinLabel;
	QLabel * m_message;
	QSpinBox * m_spinBox;
    QDialogButtonBox * m_buttonBox;
};

class ArrowButton : public QLabel {
	Q_OBJECT

public:
	ArrowButton(int scrollX, int scrollY, ZoomableGraphicsView * view, const QString & path);

protected:
	void mousePressEvent(QMouseEvent *event);

protected:
	int m_scrollX;
	int m_scrollY;
	ZoomableGraphicsView * m_view;
};


#endif 
